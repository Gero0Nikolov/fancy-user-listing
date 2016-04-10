jQuery( document ).ready(function(){
	jQuery( "#pic_users" ).on("click", function(){
		loadUserPicker();
	});
});

function addUserToListing( userID ) {
	if ( jQuery( "#user-"+userID ).hasClass( "selected" ) ) {
		jQuery( "#user-"+userID ).removeClass( "selected" );

		current_lisiting = jQuery( "#custom_users_listing" ).val().split( "," );

		indexOfID = current_lisiting.indexOf( userID );
		if ( indexOfID > -1 ) {
			current_lisiting.splice( indexOfID, 1 );
			jQuery( "#custom_users_listing" ).val( current_lisiting.toString() );
		} else {
			console.log( "ID not found: "+ userID );
		}
	} else {
		jQuery( "#user-"+userID ).addClass( "selected" );

		current_lisiting = jQuery( "#custom_users_listing" ).val().split( "," );
		current_lisiting.push( userID );
		jQuery( "#custom_users_listing" ).val( current_lisiting.toString() );
	}
}

function loadUserPicker() {
	if ( jQuery( "#user_picker" ).length > 0 ) {
		jQuery( "#user_picker" ).hide();
		jQuery( "#quantity_holder" ).show();
		setTimeout(function(){ 
			jQuery( "#user_picker").remove(); 
		}, 250);
	} else {
		picker_ = "<div id='user_picker' class='pick-box'><div class='loader'></div></div>";
		jQuery( picker_ ).insertAfter( "#pic_users" );
		jQuery( "#quantity_holder" ).hide();
		jQuery( "#user_picker" ).show();
	
		//Make the AJAX Call
		jQuery.post(
		    ajaxurl, 
		    {
		        'action': 'ful_load_user_picker',
		        'data': ""
		    }, 
		    function(response){
		    	if ( response == "-1" ) {
		    		console.log( "Something went very wrong in function: ful_pull_user();" );
		    	} else {
		    		jQuery( ".pick-box .loader" ).remove();
		    		jQuery( "#user_picker" ).append( response );
		    		setTimeout(function(){
		    			jQuery( "#user_picker #users-list" ).addClass( "normalize-location-y" );
		    		}, 150);

		    		highLightUIDs();
		    	}
		    }
		);
	}
}

function highLightUIDs() {
	userIDs = jQuery( "#custom_users_listing" ).val().split( "," );
	for( id = 0; id < userIDs.length; id++ ) {
		jQuery( "#user-"+ userIDs[id] ).addClass( "selected" );
	}
}