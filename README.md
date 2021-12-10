# ManishJoy_ProductUrlRewrite v1.0.0
## Module Purpose
### This extension adds console commands to regenerate product URLs:
1. Can generate URL rewrites for specific product(s)
2. Can generate URL rewrites for all products at once

___________________________________________________________________________________________________

## Features:
1. Easy to install & use
2. Open Source

___________________________________________________________________________________________________

## Installation:
1. Go to Magento2 root folder

2. Create a directory **app/code/Magento/ManishJoy/ProductUrlRewrite**

3. Enter following commands to enable module:

    ```bash
    bin/magento setup:upgrade
    bin/magento setup:static-content:deploy -f
    ```
4. Now, you are all set to go, use following instructions to run console commands

___________________________________________________________________________________________________

## Usage Instructions:
1. Log into Terminal/SSH/Putty

2. Navigate to root directory of Magento (e.g. `/var/www/html`)

3. Enter following commands to enable module:

    ```bash
    # Regenerate url for all products
    php bin/magento mj:producturlrewrite:run

    # Regenerate url for products with ids (1, 2, 3, 4)
    php bin/magento mj:producturlrewrite:run 1 2 3 4
    ```
4. Now, you are all set to go, use following instructions to run console commands

___________________________________________________________________________________________________

### The extension is free and always will be

## Additional Charges:
#### Installation: $ 20
#### Support: $ 40 /6 months

___________________________________________________________________________________________________
## Liked my work?

<a href="https://www.paypal.me/manishjoy" rel="nofollow"><img height="36" src="https://manishjoy.github.io/img/coffee-btn-image.png" border="0" alt="Buy Me a Coffee" data-canonical-src="https://manishjoy.github.io/img/coffee-btn-image.png" style="max-width:100%;"></a>

--- OR ---

<a href='https://www.patreon.com/manishjoy' target='_blank'><img src='https://i.ibb.co/rHdTFtj/patreon-btn.jpg' width='200' border='0' alt='SUPPORT ME ON PATREON' /></a>

___________________________________________________________________________________________________
## Prerequisites

### Use the following table to verify you have the correct prerequisites to install this Extension.
<table>
	<tbody>
		<tr>
			<th>Prerequisite</th>
			<th>How to check</th>
			<th>For more information</th>
		</tr>
	<tr>
		<td>Apache 2.2 or 2.4</td>
		<td>Ubuntu: <code>apache2 -v</code><br>
		CentOS: <code>httpd -v</code></td>
		<td><a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/apache.html">Apache</a></td>
	</tr>
	<tr>
		<td>PHP 7.1.x</td>
		<td><code>php -v</code></td>
		<td><a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/php-ubuntu.html">PHP Ubuntu</a><br><a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/php-centos.html">PHP CentOS</a></td>
	</tr>
	<tr><td>MySQL 5.6.x</td>
	<td><code>mysql -u [root user name] -p</code></td>
	<td><a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/mysql.html">MySQL</a></td>
	</tr>
</tbody>
</table>

___________________________________________________________________________________________________
## Feedback and Support

 - <a href="https://www.manishjoy.com/">https://www.manishjoy.com</a>

 - <a href="mailto:support@manishjoy.com">support@manishjoy.com</a>

## Tutorials and Blogs

 - <a href="https://blog.manishjoy.com/">https://blog.manishjoy.com</a>

 - <a href="https://blog.manishjoy.com/magento-development-guide/">https://blog.manishjoy.com/magento-development-guide/</a>
