
# In wp-config.php:
/* BEE Force the admin area to HTTPS */
define('FORCE_SSL_ADMIN', false);

# DB Host
define('DB_HOST', 'localhost');

# Grant authority to the database
grant all on db673749657.* to 'dbo673749657'@'localhost' identified by 'Pangolin625';

# Edit database
http://localhost/phpmyadmin/

# Browser.  Chrome may not work, so use Firefox instead.

# Debuging:

print '<pre/>';print_r();die('stopped');


[roles] => Array
(
	[0] => administrator
	[1] => editor
    [2] => author
    [3] => contributor
    [4] => subscriber
    [5] => association_admin
    [6] => baf_committee_view
    [7] => baf_committee_full
    [8] => bbp_keymaster
)