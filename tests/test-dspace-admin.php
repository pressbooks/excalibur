<?php

class DspaceAdminTest extends \WP_UnitTestCase {


	function test_formHelpers() {

		$admin = new \Excalibur\Dspace\Admin();

		ob_start();
		$admin->displayPasswordInput( 'name', 'value', 'label', 'description', false, 'class' );
		$buffer = ob_get_clean();
		$this->assertContains( '<input ', $buffer );
		$this->assertContains( 'type="password"', $buffer );

		ob_start();
		$admin->displaySelect( 'name', [ 1 => 'One', 2 => 'Two' ], 1, 'label', 'description', false, false, false );
		$buffer = ob_get_clean();
		$this->assertContains( '<select', $buffer );
		$this->assertEquals( substr_count( $buffer, "selected='selected'" ), 1 );

		ob_start();
		$admin->displaySelect( 'name', [ 1 => 'One', 2 => 'Two' ], [ 1, 2 ], 'label', 'description', false, true, true );
		$buffer = ob_get_clean();
		$this->assertContains( '<select', $buffer );
		$this->assertContains( 'name[]', $buffer );
		$this->assertContains( 'disabled', $buffer );
		$this->assertEquals( substr_count( $buffer, "selected='selected'" ), 2 );

		ob_start();
		$admin->displayTextArea( 'name', 'value', 'label', 'description', false );
		$buffer = ob_get_clean();
		$this->assertContains( '<textarea ', $buffer );

		ob_start();
		$admin->displayTextInput( 'name', 'value', 'label', 'description', false, 'class' );
		$buffer = ob_get_clean();
		$this->assertContains( '<input ', $buffer );
		$this->assertContains( 'type="text" ', $buffer );

		ob_start();
		$admin->displayTextInputRows( 'name', 'value', 'label', 'description', 'class' );
		$buffer = ob_get_clean();
		$this->assertContains( '<input ', $buffer );
		$this->assertContains( 'type="text" ', $buffer );
		$this->assertContains( 'Add Row</button>', $buffer );
	}
}
