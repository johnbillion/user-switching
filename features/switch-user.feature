Feature: Switch users
	As an administrator
	I need to be able to switch between users
	In order to access different user accounts

	Background:
		Given the "user-switching/user-switching.php" plugin is active
		And there are users:
			| user_login    | display_name | user_email                | user_pass | role          |
			| editor        | Editor       | editor@example.com        | password  | editor        |
			| author        | Author       | author@example.com        | password  | author        |

	Scenario: Switch to editor and back
		Given I am logged in as admin
		When I switch to user "editor"
		Then I should be logged in as "editor"
		When I switch back to "admin"
		Then I should be logged in as "admin"

	Scenario: Switch off and back
		Given I am logged in as admin
		When I switch off
		Then I should be logged out
		When I switch back to "admin"
		Then I should be logged in as "admin"
