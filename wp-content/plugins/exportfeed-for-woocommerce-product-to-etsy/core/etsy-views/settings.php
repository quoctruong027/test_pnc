<div class="wrap">
    <?php if ($this->state == 'active') { ?>
        <div class="updated settings-error"><p style="color:red">Etsy charges standard fees for active listings. <a
                        href="https://www.etsy.com/legal/fees/" target="_blank">Check Etsy pricing here</a> . You can go
                to
                configuration page and change state to Draft in order to prevent charges.</p></div>
    <?php } ?>
    <h2>Etsy Configuration</h2>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-2" class="postbox-container settings">

                <div class="inside export-target">
                    <table class="form-table settings">
                        <tbody>
                        <?php
                        $whenMade = array('made_to_order' => 'Made to order',
                            '2010_2019' => 'Between 2010 to 2019',
                            '2000_2009' => 'Between 2000 to 2009',
                            'before_2000' => ' Before 2000',
                            '1990s' => "90s Product", '1980s' => '80s Product', '1970s' => '70s Product', '1960s' => '60s Product',
                            '1950s' => '50s product', '1940s' => '40s Product', '1930s' => '30s Product', '1920s' => '20s Product',
                            '1910s' => '1910s product', '1900s' => '1900s Product', '1800s' => '1800s Product', '1700s' => '1700s Product', 'before_1700' => 'Before 1700');
                        foreach ($this->config as $key => $config) :
                            if ($config->configuration_title == 'who_made')
                                $title = 'Who Made It?';
                            if ($config->configuration_title == 'when_made')
                                $title = 'When It Was Made?';
                            if ($config->configuration_title == 'state')
                                $title = 'State';
                            if ($config->configuration_title == 'etsy_api_limit')
                                $title = 'Etsy Api Limit';
                            if ($config->configuration_title == 'etsy_calculated_shipping')
                                $title = 'Active Calculated Shipping Template ID';
                            ?>
                            <tr>
                                <th><?php echo $title ?></th>
                                <td style="width:90px">
                                    <?php
                                    if ($title == 'When It Was Made?') { ?>
                                        <select name="<?php echo $config->configuration_title; ?>" id="<?php echo $config->configuration_title; ?>"
                                                value="">
                                            <option value="0">Select Option</option>
                                            <?php foreach ($whenMade as $key => $item) { ?>
                                                <option <?php if($config->configuration_value==$key) echo 'selected'; ?> value="<?php echo $key; ?>"><?php echo $item; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php
                                    } else {
                                        $this->parseSettingOptions($config);
                                    } ?>
                                    <p class="desc"><?php echo $config->configuration_description; ?></p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>
                                <button class="button button-primary" id="update_account"
                                        onclick="changeSettingsofEtsy()">Update
                                </button>
                            </th>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
