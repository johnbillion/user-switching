Feature: Switch users
	As an administrator
	I need to be able to switch between users
	In order to access different user accounts

	Background:
		Given I have a WordPress installation
			| name      | email                     | username      | password |
			| WordPress | administrator@example.com | administrator | password |
		And there are plugins
			| plugin                            | status  |
			| user-switching/user-switching.php | enabled |
		And there are users
			| user_login    | display_name | user_email                | user_pass | role          |
			| editor        | Editor       | editor@example.com        | password  | editor        |
			| author        | Author       | author@example.com        | password  | author        |
			| contributor   | Contributor  | contributor@example.com   | password  | contributor   |
			| subscriber    | Subscriber   | subscriber@example.com    | password  | subscriber    |
			| none          | None         | none@example.com          | password  |               |

	Scenario: Switch to editor
		Given I am logged in as "administrator" with password "password"
		When I switch to user "editor"
		Then I should be logged in as "editor"
