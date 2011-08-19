<div class="wrap">
  <div id="icon-options-general" class="icon32"><br></div>
  <h2>Google Analytics</h2>
  
  <form method="post" action="options.php">
    
    <?php settings_fields('google-analytics-auth') ?>
    
    <p>Please provide the Google Accounts <b>e-mail</b> and <b>password</b> with which you wish to log into Google Analytics.</p>
  
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row">
            <label for="<?php echo self::EMAIL_SETTING ?>">E-mail Address</label>
          </th>
          <td>
            <input type="text" style="width:200px;" name="<?php echo self::EMAIL_SETTING ?>" id="<?php echo self::EMAIL_SETTING ?>" value="<?php echo htmlentities(get_option(self::EMAIL_SETTING)) ?>" />
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="<?php echo self::PASSWORD_SETTING ?>">Password</label>
          </th>
          <td>
            <input type="password" name="<?php echo self::PASSWORD_SETTING ?>" id="<?php echo self::PASSWORD_SETTING ?>" value="" />
          </td>
        </tr>
      </tbody>
    </table>
    
    <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo _e('Save and Login') ?>"></p>
    
  </form>
  
</div>