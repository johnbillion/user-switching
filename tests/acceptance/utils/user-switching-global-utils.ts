import { GlobalUtils, GlobalUtilsOptions } from '@johnbillion/plugin-infrastructure/acceptance';

export class UserSwitchingGlobalUtils extends GlobalUtils {
	constructor( options: GlobalUtilsOptions ) {
		super( options );
	}
	installWordPress() {
		// Call parent method for basic WordPress setup
		super.installWordPress();

		// Install language packs for user-switching testing:
		this.runWPCLICommand( 'language core install it_IT' );
		this.runWPCLICommand( 'language plugin install user-switching it_IT' );
	}
}
