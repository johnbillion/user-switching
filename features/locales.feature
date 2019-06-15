Feature: Locale support
	As an administrator of a site which uses more than one language
	I need to be able to switch between users
	And see User Switching's output in my original language

	Background:
		Given the "user-switching/user-switching.php" plugin is active
		And there are users:
			| user_login    | display_name | user_email                | user_pass | role          | locale |
			| autore        | Autore       | autore@example.com        | password  | author        | it_IT  |

	Scenario: Switch to author and back
		Given I am logged in as admin
		When I switch to user "autore"
        Then the page language should be "it-IT"
		But I should see a status message that says "Switched to Autore"

		When I go to the dashboard
		And I switch back to "admin"
        Then the page language should be "en-US"
		And I should see a status message that says "Switched back to admin"
