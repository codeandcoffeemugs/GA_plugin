<style>
tr.selected td { font-weight: bold; }
</style>

<div class="wrap">
  <div id="icon-options-general" class="icon32"><br></div>
  <h2>Google Analytics</h2>
  
  <br />
  
  <form method="post" action="options.php">
    
    <?php settings_fields('google-analytics-account') ?>
  
    <?php if (!$accounts) { ?>
      
      <!-- TODO: something about no accounts... blah blah blah -->
      
    <?php } else { ?>
      
      <table class="wp-list-table widefat fixed pages" cellspacing="0">
        <thead>
          <tr>
            <th scope="col" id="cb" class="manage-column column-cb check-column" style=""></th>
            <th scope="col" id="title" class="manage-column column-title" style="">
              <span>Title</span>
            </th>
            <th>
              <span>Web Property ID</span>
            </th>
          </tr>
        </thead>

        <tfoot>
          <tr>
            <th scope="col" id="cb" class="manage-column column-cb check-column" style=""></th>
            <th scope="col" id="title" class="manage-column column-title" style="">
              <span>Title</span>
            </th>
            <th>
              <span>Web Property ID</span>
            </th>
          </tr>
        </tfoot>
        
        <tbody id="the-list">
          <?php foreach($accounts as $i => $account) { $encoded = json_encode($account); ?>
            <tr class="<?php if ($i % 2) echo 'alternate' ?> <?php if ($encoded == get_option(self::ACCOUNT_SETTING)) echo 'selected' ?>">
              <td><input type="radio" value="<?php echo htmlentities($encoded) ?>" name="<?php echo self::ACCOUNT_SETTING ?>" <?php if ($encoded == get_option(self::ACCOUNT_SETTING)) echo 'checked="checked"' ?> /></td>
              <td><?php echo $account->title ?></td>
              <td><?php echo $account->webPropertyId ?></td>
            </tr>
          <?php } ?>
        </tbody>

      </table>
    
    <?php } ?>
    
    <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo _e('Save Changes') ?>"></p>
    
  </form>
  
  <form method="post" action="options.php">
    
    <?php settings_fields('google-analytics-auth') ?>
  
    <p class="submit"><input type="submit" name="submit" id="submit" class="button" value="<?php echo _e('Logout') ?>"></p>
    
  </form>
  
</div>