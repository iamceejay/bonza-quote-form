<?php
use PHPUnit\Framework\TestCase;

class QuoteTest extends TestCase {
    
    public function setUp(): void {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->prefix}bonza_quotes WHERE email LIKE '%@test.com'");
        echo "✓ Test data cleaned\n";
    }

    public function test_quote_crud_operations() {
        echo "\nTesting CRUD Operations\n";
        echo "======================\n";
        
        echo "Creating quote... ";
        $quote = new Bonza_Quote_Form_Quote([
            'name' => 'John Doe',
            'email' => 'john@test.com', 
            'service_type' => 'Web Design',
            'notes' => 'Test quote',
            'status' => 'pending'
        ]);
        
        $quote_id = $quote->save();
        $this->assertIsInt($quote_id);
        $this->assertGreaterThan(0, $quote_id);
        echo "✓ Created ID: {$quote_id}\n";
        
        echo "Retrieving quote... ";
        $retrieved = Bonza_Quote_Form_Quote::get_by_id($quote_id);
        $this->assertNotNull($retrieved);
        $this->assertEquals('John Doe', $retrieved->name);
        $this->assertEquals('pending', $retrieved->status);
        echo "✓ Retrieved: {$retrieved->name}\n";
        
        echo "Updating status... ";
        $result = Bonza_Quote_Form_Quote::update_status($quote_id, 'approved');
        $this->assertTrue($result);
        
        $updated = Bonza_Quote_Form_Quote::get_by_id($quote_id);
        $this->assertEquals('approved', $updated->status);
        echo "✓ Status: pending → approved\n";
        
        echo "Deleting quote... ";
        $delete_result = Bonza_Quote_Form_Quote::delete($quote_id);
        $this->assertTrue($delete_result);
        
        $deleted = Bonza_Quote_Form_Quote::get_by_id($quote_id);
        $this->assertNull($deleted);
        echo "✓ Deleted successfully\n";
    }
    
    public function test_quote_validation() {
        echo "\nTesting Validation\n";
        echo "==================\n";
        
        echo "Testing invalid data... ";
        $invalid_quote = new Bonza_Quote_Form_Quote([
            'name' => '',
            'email' => 'invalid-email',
            'service_type' => 'Web Design'
        ]);
        
        $validation = $invalid_quote->validate();
        $this->assertInstanceOf('WP_Error', $validation);
        echo "✗ Correctly rejected\n";
        
        echo "Testing valid data... ";
        $valid_quote = new Bonza_Quote_Form_Quote([
            'name' => 'Jane Smith',
            'email' => 'jane@test.com',
            'service_type' => 'SEO',
            'status' => 'pending'
        ]);
        
        $validation = $valid_quote->validate();
        $this->assertTrue($validation);
        echo "✓ Validation passed\n";
    }
    
    public function tearDown(): void {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->prefix}bonza_quotes WHERE email LIKE '%@test.com'");
    }
}