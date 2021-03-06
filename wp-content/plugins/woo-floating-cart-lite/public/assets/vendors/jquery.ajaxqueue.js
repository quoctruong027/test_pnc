(function($) {

	// jQuery on an empty object, we are going to use this as our Queue
	var XT_Ajax_Queue = $({});
	
	$.XT_Ajax_Queue = function( ajaxOpts ) {
	    var jqXHR,
	        dfd = $.Deferred(),
	        promise = dfd.promise();
	
	    // run the actual query
	    function doRequest( next ) {
	        jqXHR = $.ajax( ajaxOpts );
	        jqXHR.done( dfd.resolve )
	            .fail( dfd.reject )
	            .then( next, next );
	    }
	
	    // queue our ajax request
	    XT_Ajax_Queue.queue( doRequest );
	
	    // add the abort method
	    promise.abort = function( statusText ) {
	
	        // proxy abort to the jqXHR if it is active
	        if ( jqXHR ) {
	            return jqXHR.abort( statusText );
	        }
	
	        // if there wasn't already a jqXHR we need to remove from queue
	        var queue = XT_Ajax_Queue.queue(),
	            index = $.inArray( doRequest, queue );
	
	        if ( index > -1 ) {
	            queue.splice( index, 1 );
	        }
	
	        // and then reject the deferred
	        dfd.rejectWith( ajaxOpts.context || ajaxOpts, [ promise, statusText, "" ] );
	        return promise;
	    };
	
	    return promise;
	};

})(jQuery);