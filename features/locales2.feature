Feature: Locale support
	As an administrator of a site which uses more than one language
	I need to be able to switch between users
	And see User Switching's output in my original language

	Background:
		Given the "user-switching/user-switching.php" plugin is active
		And there are users:
			| user_login    | display_name | user_email                | user_pass | role          | locale |
			| admin_it      | Admin IT     | admin_it@example.com      | password  | administrator | it_IT  |
			| author_en     | Author EN    | author_en@example.com     | password  | author        |        |

	Scenario: Switch from Italian admin to English author and back
		Given I am logged in as admin_it
		When I switch to user "author_en"
        Then the page language should be "en-US"
		But I should see a status message that says "Cambiato a Author EN"
		And the "#user_switching p" element language should be "it-IT"

		When I go to the dashboard
		And I switch back to "admin_it"
        Then the page language should be "it-IT"
		And I should see a status message that says "Tornato a Admin IT"
