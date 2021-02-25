<?php

/* Leave no trace */

// Delete our option
delete_option( 'cptpro_settings' );

// Delete our transient
delete_transient( 'cptpro-license-details' );