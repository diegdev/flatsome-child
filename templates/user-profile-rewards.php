<?php
defined( 'ABSPATH' ) || exit;
extract($args);
//echo $user->ID;
?>
<table class="form-table">
    <tr>
        <th>
            <label for="code"><?php _e( 'Rewards' ); ?></label>
        </th>
        <td>
            <input type="text" name="_user_rewards" id="_user_rewards" value="<?php echo esc_attr( get_user_meta( $user->ID, '_user_rewards', true ) ); ?>" class="regular-text" />
        </td>
    </tr>
</table>
