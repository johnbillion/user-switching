# Releasing a New Version

These are the steps to take to release a new version of User Switching (for contributors who have push access to the GitHub repo).

## Prior to Release

1. Check [the milestone on GitHub](https://github.com/johnbillion/user-switching/milestones) for open issues or PRs. Fix or reassign as necessary.
1. If this is a non-patch release, check issues and PRs assigned to the patch or minor milestones that will get skipped. Reassign as necessary.
1. Ensure you're on the `develop` branch and all the changes for this release have been merged in.
1. Ensure `readme.md` and `readme.txt` contain up to date "Tested up to" versions, descriptions, FAQs, screenshots, etc.
1. Ensure `.gitattributes` is up to date with all files that shouldn't be part of the build.
   - To do this, run `git archive --output=user-switching.zip HEAD` then check the contents for files that shouldn't be part of the package.
1. Run `composer test` and ensure everything passes.
1. Run `git push origin develop` (if necessary) and ensure CI is passing.
1. Prepare a changelog for [the Releases page on GitHub](https://github.com/johnbillion/user-switching/releases).

## For Release

1. Bump the plugin version number:
   - `npm run bump:patch` for a patch release (1.2.3 => 1.2.4)
   - `npm run bump:minor` for a minor release (1.2.3 => 1.3.0)
   - `npm run bump:major` for a major release (1.2.3 => 2.0.0)
1. `git push origin develop`
1. `git push origin --tags`
1. Enter the changelog into [the release on GitHub](https://github.com/johnbillion/user-switching/releases) and publish it.
1. Approve the release on [the WordPress.org release management dashboard](https://wordpress.org/plugins/developers/releases/).
1. `git push origin develop:trunk`

## Post Release

Publishing a release on GitHub triggers an action which deploys the release to the WordPress.org Plugin Directory. No need to touch Subversion.

New milestones are automatically created for the next major, minor, and patch releases where appropriate.

1. If this is a non-patch release, manually delete any [unused patch and minor milestones on GitHub](https://github.com/johnbillion/user-switching/milestones).
1. Resolve relevant threads on [the plugin's support forums](https://wordpress.org/support/plugin/user-switching/).

## Asset Updates

Assets such as screenshots and banners are stored in the `.wordpress-org` directory. These get deployed as part of the automated release process too.

In order to deploy only changes to assets and the readme file, push the change to the `deploy` branch. This allows for the "Tested up to" value to be bumped as well as assets to be updated in between releases. Changes to files other than assets and the readme file will be ignored.
