jQuery( document ).ready(function(){
	jQuery( ".fancy-user-trigger" ).on("click", function(){
		//Make the AJAX Call
		userID = jQuery( this ).attr( "id" ).split( "user-" )[1];
		jQuery.post(
		    ajaxurl, 
		    {
		        'action': 'ful_pull_user',
		        'data': userID +"&"+ posts_to_reveal,
		    }, 
		    function(response){
		    	if ( response == "-1" ) {
		    		console.log( "Something went very wrong in function: ful_pull_user();" );
		    	} else {
		    		jQuery( ".fancy-user-list-popup .loader" ).remove();
		    		jQuery( ".fancy-user-list-popup .content" ).append( response );
		    		setTimeout(function(){
		    			jQuery( ".fancy-user-list-popup" ).find( "#top" ).addClass( "normalize-location-y" );
		    			jQuery( ".fancy-user-list-popup" ).find( "#bottom" ).addClass( "normalize-location-y" );
		    		}, 150);
		    	}
		    }
		);

		//Build the container
		container_ = "\
		<div class='fancy-user-list-popup'>\
			<div class='close-button'></div>\
			<div class='content'>\
				<div class='loader'>\
				</div>\
			<div>\
		</div\
		";

		jQuery( "body" ).append( container_ );
		jQuery( ".fancy-user-list-popup" ).fadeIn( "fast" ).children( ".content" ).slideDown( "medium" );
		setTimeout(function(){ jQuery( ".fancy-user-list-popup .close-button" ).fadeIn( "fast") }, 250);

		jQuery( ".fancy-user-list-popup .close-button" ).on("click", function(){ closeFancyPopup(); });
			jQuery( ".fancy-user-list-popup" ).on('click', function(e) { 
			if( e.target == this ) closeFancyPopup(); 
		});
	});
});

function closeFancyPopup() {
	jQuery( ".fancy-user-list-popup" ).fadeOut( "fast" );
	setTimeout(function(){ jQuery( ".fancy-user-list-popup" ).remove(); }, 150);
}