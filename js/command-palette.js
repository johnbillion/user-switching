/**
 * Command Palette integration for User Switching.
 */

( function () {
	const commandsStore = wp.commands.store;
	const coreStore = wp.coreData.store;
	const dispatch = wp.data.dispatch;
	const useSelect = wp.data.useSelect;
	const useState = wp.element.useState;
	const useEffect = wp.element.useEffect;
	const useMemo = wp.element.useMemo;
	const useDebounce = wp.compose.useDebounce;
	const __ = wp.i18n.__;
	const sprintf = wp.i18n.sprintf;
	const el = wp.element.createElement;
	const SVG = wp.primitives.SVG;
	const Path = wp.primitives.Path;

	function useDebouncedValue( value ) {
		const [ debouncedValue, setDebouncedValue ] = useState( '' );
		const debounced = useDebounce( setDebouncedValue, 250 );
		useEffect( function () {
			debounced( value );
			return function () {
				debounced.cancel();
			};
		}, [ debounced, value ] );
		return debouncedValue;
	}

	const settings = window.userSwitchingCommands;

	if ( ! settings ) {
		return;
	}

	const switchIcon = el(
		SVG,
		{
			xmlns: 'http://www.w3.org/2000/svg',
			viewBox: '0 0 24 24',
		},
		el( Path, {
			d: 'M17.5 9a2 2 0 11-4 0 2 2 0 014 0zm-4.25 8v-2a2.75 2.75 0 00-2.75-2.75h-4A2.75 2.75 0 003.75 15v2h1.5v-2c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2h1.5zm7-2v2h-1.5v-2c0-.69-.56-1.25-1.25-1.25H15v-1.5h2.5A2.75 2.75 0 0120.25 15zM8.5 11a2 2 0 100-4 2 2 0 000 4z',
		} )
	);

	// Static command: Switch Back.
	if ( settings.switchBackUrl ) {
		dispatch( commandsStore ).registerCommand( {
			name: 'user-switching/switch-back',
			label: settings.switchBackLabel,
			icon: settings.switchBackAvatar ? el( 'img', {
				src: settings.switchBackAvatar,
				alt: '',
			} ) : switchIcon,
			callback: function ( args ) {
				document.location.href = settings.switchBackUrl;
				args.close();
			},
		} );
	}

	// Static command: Switch Off.
	if ( settings.switchOffUrl ) {
		dispatch( commandsStore ).registerCommand( {
			name: 'user-switching/switch-off',
			label: settings.switchOffLabel,
			icon: switchIcon,
			callback: function ( args ) {
				document.location.href = settings.switchOffUrl;
				args.close();
			},
		} );
	}

	// Dynamic command loader for searching users to switch to.
	if ( settings.canSwitchUsers ) {
		dispatch( commandsStore ).registerCommandLoader( {
			name: 'user-switching/switch-to-user',
			hook: function useSwitchToUserLoader( options ) {
				const search = useDebouncedValue( options.search );

				const { users, isLoading } = useSelect( function ( select ) {
					if ( ! search ) {
						return { users: [], isLoading: false };
					}

					const query = {
						search: search,
						per_page: 10,
						context: 'view',
					};
					const core = select( coreStore );

					return {
						users: core.getEntityRecords( 'root', 'user', query ) || [],
						isLoading: ! core.hasFinishedResolution( 'getEntityRecords', [ 'root', 'user', query ] ),
					};
				}, [ search ] );

				const commands = useMemo( function () {
					return users
						.filter( function ( user ) {
							return user.user_switching_url;
						} )
						.map( function ( user ) {
							const avatarUrl = user.avatar_urls && ( user.avatar_urls[ '48' ] || user.avatar_urls[ '24' ] );

							return {
								name: `user-switching/switch-to-${ user.id }`,
								/* translators: %s: User's display name. */
								label: sprintf( __( 'Switch to %s', 'user-switching' ), user.name ),
								searchLabel: `Switch to ${ user.name } ${ user.slug }`,
								icon: avatarUrl ? el( 'img', {
									src: avatarUrl,
									alt: '',
								} ) : switchIcon,
								callback: function ( args ) {
									document.location.href = user.user_switching_url;
									args.close();
								},
							};
						} );
				}, [ users ] );

				return {
					commands: commands,
					isLoading: isLoading,
				};
			},
		} );
	}
} )();
