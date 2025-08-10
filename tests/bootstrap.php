<?php
echo "Bonza Quote Form - Test Suite\n";
echo "============================\n";

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';
echo "✓ WordPress loaded\n";

require_once dirname(__DIR__) . '/includes/class-bonza-quote-form-activator.php';
require_once dirname(__DIR__) . '/includes/class-bonza-quote-form-quote.php';
echo "✓ Plugin classes loaded\n";

Bonza_Quote_Form_Activator::activate();
echo "✓ Database ready\n\n";