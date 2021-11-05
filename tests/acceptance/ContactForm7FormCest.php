<?php
use Codeception\Util\Locator;

/**
 * Tests for ConvertKit Forms integration with Contact Form 7.
 * 
 * @since 	1.0.0
 */
class ContactForm7FormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
    public function _before(AcceptanceTester $I)
    {
    	$I->activateConvertKitPlugin($I);
    	$I->setupConvertKitPlugin($I);

    	// Go to the Plugins screen in the WordPress Administration interface.
        $I->amOnPluginsPage();

        // Activate the Contact Form 7 Plugin.
        $I->activatePlugin('contact-form-7');

        // Check that the Plugin activated successfully.
        $I->seePluginActivated('contact-form-7');

        // Check that no PHP warnings or notices were output.
    	$I->checkNoWarningsAndNoticesOnScreen($I);
    }

    /**
     * Test that the Contact Form 7 Settings tab and screen exist in the Plugin Settings
     * and load without errors.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
    public function testSettingsContactForm7Loads(AcceptanceTester $I)
    {
    	// Load Plugin Settings
    	$I->amOnAdminPage('options-general.php?page=_wp_convertkit_settings');

    	// Check Contact Form 7 tab exists.
    	$I->see('Contact Form 7', 'h2.nav-tab-wrapper a.nav-tab');

    	// Load Contact Form 7 Plugin Settings
    	$I->amOnAdminPage('options-general.php?page=_wp_convertkit_settings&tab=contactform7');

    	// Check that no PHP warnings or notices were output.
    	$I->checkNoWarningsAndNoticesOnScreen($I);
    }

    /**
     * Test that saving a Contact Form 7 to ConvertKit Form Mapping works.
	 * 
	 * @since 	1.0.0
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
    public function testSettingsContactForm7ToConvertKitFormMapping(AcceptanceTester $I)
    {
    	// Get Contact Form 7 Form ID.
    	$contactForm7ID = $this->_getContactForm7ID($I);

    	// Load Contact Form 7 Plugin Settings
    	$I->amOnAdminPage('options-general.php?page=_wp_convertkit_settings&tab=contactform7');

    	// Check that no PHP warnings or notices were output.
    	$I->checkNoWarningsAndNoticesOnScreen($I);

    	// Check that a Form Mapping option is displayed.
    	$I->seeElementInDOM('#_wp_convertkit_integration_contactform7_settings_' . $contactForm7ID);

    	// Change Form to value specified in the .env file.
    	$I->selectOption('#_wp_convertkit_integration_contactform7_settings_' . $contactForm7ID, $_ENV['CONVERTKIT_API_FORM_NAME']);

    	$I->click('Save Changes');

    	// Check that no PHP warnings or notices were output.
    	$I->checkNoWarningsAndNoticesOnScreen($I);

    	// Check the value of the Form field matches the input provided.
    	$I->seeOptionIsSelected('#_wp_convertkit_integration_contactform7_settings_' . $contactForm7ID, $_ENV['CONVERTKIT_API_FORM_NAME']);
    
    	// Create Page with Contact Form 7 Shortcode.
    	$I->havePageInDatabase([
    		'post_title'	=> 'ConvertKit: Contact Form 7 Shortcode',
    		'post_name' 	=> 'convertkit-contact-form-7-shortcode',
    		'post_content' 	=> 'Form:
[contact-form-7 id="' . $contactForm7ID . '"]',
    	]);

		// Load the Page on the frontend site.
	    $I->amOnPage('/convertkit-contact-form-7-shortcode');

	    // Check that no PHP warnings or notices were output.
    	$I->checkNoWarningsAndNoticesOnScreen($I);

    	// Complete Name and Email
    	$I->fillField('input[name=your-name]', 'ConvertKit Name');
    	$I->fillField('input[name=your-email]', $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']);
    	$I->fillField('input[name=your-subject]', 'ConvertKit Subject');

    	// Submit Form.
    	$I->click('Submit');

    	// Confirm the form submitted without errors.
    	$I->performOn( 'form.sent', function($I) {
    		$I->seeInSource('Thank you for your message. It has been sent.');
    	});
    }

    /**
     * Creates a Contact Form 7 Form
     * 
     * @since 	1.0.0
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 * @return 	int 					Form ID
	 */
    private function _createContactForm7Form(AcceptanceTester $I)
    {
    	return $I->havePostInDatabase([
    		'post_name' 	=> 'contact-form-7-form',
    		'post_title'	=> 'Contact Form 7 Form',
    		'post_content'	=> '[text* your-name] [email* your-email] [text* your-subject] [textarea your-message] [submit "Submit"]',
    		'post_type'		=> 'wpcf7_contact_form',
    		'post_status'	=> 'publish',
    	]);
    }

    /**
     * Gets the first(default) Contact Form 7 Form
     * 
     * @since 	1.0.0
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 * @return 	int 					Form ID
	 */
    private function _getContactForm7ID(AcceptanceTester $I)
    {
    	$result = $I->grabAllFromDatabase($I->grabPrefixedTableNameFor('posts'), 'ID', [
    		'post_type' => 'wpcf7_contact_form',
    	]);

    	return $result[0]['ID'];
    }
}