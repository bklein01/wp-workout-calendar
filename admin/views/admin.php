<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Real_Advocacy
 * @author    Ben Klein <bklein@orangehatgroup.com>
 * @license   GPL-2.0+
 * @link      http://www.realadvocacy.com
 * @copyright 2014 Ben Klein
 */
?>

<?php
if (isset($_POST['update_settings'])) {
    $clientId = $_POST['advocacy_client_id'];
    update_option("realadvocacy_client_id", $clientId);
    $secret = $_POST['advocacy_client_secret'];
    update_option("realadvocacy_client_secret", $secret);
}
$clientId = get_option("realadvocacy_client_id");
$secret = get_option("realadvocacy_client_secret");
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

        <form method="post">
            <input type="hidden" name="update_settings" value="Y" />
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="advocacy_client_id">
                            Client ID:
                        </label> 
                    </th>
                    <td>
                        <input value="<?php echo $clientId ?>" type="text" name="advocacy_client_id" size="25" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="advocacy_client_secret">
                            Client Secret:
                        </label> 
                    </th>
                    <td>
                        <input value="<?php echo $secret ?>" type="text" name="advocacy_client_secret" size="25" />
                    </td>
                </tr>
            </table>
            <input type="submit" name="advocacy_save" value="Save" />        
        </form>
</div>
