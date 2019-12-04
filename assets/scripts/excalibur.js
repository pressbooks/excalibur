jQuery( function ( $ ) {
	$( document ).on( 'click', '#pb_contributing_authors .delete-row', function ( e ) {
		e.preventDefault();
		$( this ).parent( '.row' ).remove();
	} );
	$( document ).on( 'click', '#pb_contributing_authors .add-row', function ( e ) {
		e.preventDefault();
		$( this ).before( '<div class="row"><input type="text" name="pb_contributing_authors[]" value="" class="contributing-author regular-text" /> <button class="button button-small delete-row">Delete Row</button></div>' )
	} );
	$( '#pb_publication_date' ).datepicker( {
		dateFormat: 'yy-mm-dd',
	} );
} );
