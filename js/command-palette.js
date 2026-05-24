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
	const Dashicon = wp.components.Dashicon;

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

	const switchIcon = el( Dashicon, { icon: 'admin-users' } );

	// Static command: Switch Back.
	if ( settings.switchBackUrl ) {
		dispatch( commandsStore ).registerCommand( {
			name: 'user-switching/switch-back',
			label: settings.switchBackLabel,
			searchLabel: `Switch back ${ settings.switchBackLabel }`,
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
			// This facilitates searching either in English or the localized language
			searchLabel: `Switch off ${ settings.switchOffLabel }`,
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
				// This facilitates searching either in English or the localized language
				// and trims the search query to just the name.
				const trimmedSearch = options.search
					.replace( new RegExp( settings.switchToLabel, 'i' ), '' )
					.replace( /Switch to/i, '' )
					.trim();
				const search = useDebouncedValue( trimmedSearch );

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
								label: sprintf(
									/* translators: %s: User's display name. */
									__( 'Switch to %s', 'user-switching' ),
									user.name,
								),
								// This facilitates searching either in English or the localized language
								searchLabel: sprintf(
									/* translators: %s: User's display name. */
									__( 'Switch to %s', 'user-switching' ),
									`${ user.name } ${ user.slug } Switch to ${ user.name } ${ user.slug }`,
								),
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
