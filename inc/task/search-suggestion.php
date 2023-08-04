<?php
/**
 * Search suggestion
 */

// return; // transfer for @Lee

add_action('wp_footer', function() {
  $enable = get_field('search_suggestion_enable', 'option');
  if($enable != true) return;

  $suggestionData = explode(PHP_EOL, get_field('search_suggestion', 'option'));
  ?>
  <style>
    .search-suggestion {
      position: absolute;
      width: 100%;
      left: 0;
      top: 100%;
      background: white;
      color: black;
      display: none;
    }

    .search-suggestion ul {
      border: solid 1px #eee;
    }

    .search-suggestion ul li {
      list-style: none;
      margin: 0;
    }

    .search-suggestion ul li:not(:last-child) {
      border-bottom: solid 1px #eeeeee;
    }

    .search-suggestion__text {
      display: flex;
      align-items: center;
      width: 100%;
      padding: .6em 12px;
      transition: .3s ease;
      -webkit-transition: .3s ease;
      cursor: pointer;
    }

    .search-suggestion__text svg {
      margin-right: 10px;
    }

    .search-suggestion ul li:hover .search-suggestion__text {
      background: #fafafa;
    }

    .__suggestion-open .search-suggestion {
      display: block;
    }

    #wide-nav {
      position: relative;
      z-index: 9999;
    }
  </style>

  <script>
    ;((w, $) => {
      'use strict';

      /**
       * Dummy data
       */
      const suggestionData = <?php echo json_encode($suggestionData); ?>;

      const suggestionHtml = () => {
        const svgIcon = `<svg width="16px" height="16px" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg"> <path d="M244.00244,56.00513V120a12,12,0,0,1-24,0V84.9707l-75.51465,75.51465a11.99973,11.99973,0,0,1-16.9707,0L96.00244,128.9707,32.48779,192.48535a12.0001,12.0001,0,0,1-16.9707-16.9707l72-72a11.99973,11.99973,0,0,1,16.9707,0l31.51465,31.51465L203.03174,68h-35.0293a12,12,0,0,1,0-24h63.99512c.39746-.00024.79541.02075,1.1914.06006.167.01636.32911.04785.49366.071.22314.0315.44629.05786.66748.10181.19238.03809.37793.09131.56689.13843.19092.04761.3833.09009.57276.14721.18505.05616.36377.126.54492.19068.18847.06714.37793.12939.56347.2063.16846.06982.33008.1521.49415.22949.19091.08936.3833.17432.57031.27441.15527.0835.30273.17847.4541.26856.18506.10986.37207.21484.55225.33545.16455.11035.31884.2334.478.35156.15479.11523.31348.22314.46387.34692.28467.23365.55664.4812.81787.73951.019.01879.04.03418.05908.05322s.03467.04.05371.05908c.2583.262.50635.53418.73975.81885.12012.146.22461.2998.33691.45019.12159.16309.24805.32251.36133.49195.11865.177.22168.36084.33008.54272.0918.1543.189.30518.27393.46387.09863.18408.18213.37329.2705.56128.07862.16723.16211.33179.2334.50317.07569.18311.13721.37036.20362.55664.06591.18311.13623.36377.19287.551.05713.18823.09912.37964.14648.56982.04736.18946.10059.37622.13916.56909.04346.22071.07031.44361.10156.666.02344.16553.05518.32788.07129.49536Q244.00171,55.40808,244.00244,56.00513Z"/> </svg>`;
        return `<div class="search-suggestion">
          <ul>
            ${ ((_data) => {
              return _data.map(txt => {
                return `<li class="search-suggestion__item">
                  <span class="search-suggestion__text" data-string="${ txt }">
                    ${ svgIcon } 
                    ${ txt }
                  </span>
                </li>`;
              }).join('')
            })(suggestionData) }
          </ul>
        </div>`;
      }

      const searchSuggestion = ($inputSearch) => {
        const searchField = $($inputSearch);
        if(searchField.length == 0) return;

        const form = searchField.parents('form.searchform');
        form.append(suggestionHtml());
        
        const inputHasValue = () => {
          return searchField.val().length ? true : false;
        }

        const focusHandle = (noValueCB, hasValueCB) => {
          if(inputHasValue()) {
            hasValueCB();
          } else {
            noValueCB();
          }
        }

        const openSuggestion = () => {
          form.addClass('__suggestion-open');
        }

        const closeSuggestion = () => {
          form.removeClass('__suggestion-open');
        }

        $('body').on('click', '.search-suggestion__text', function(e) {
          e.preventDefault();
          e.stopPropagation();

          const searchText = $(this).data('string');
          searchField.val(searchText); 
          searchField.trigger('change');

          closeSuggestion();
          searchField.focus();
        })

        $('body').on('click', function(e) {
          if($('.searchform.__suggestion-open').length == 0) return;
          if($(e.target).parents('.__suggestion-open').length > 0) return;

          closeSuggestion();
        })

        searchField.on({
          focus(e) {
            focusHandle(openSuggestion, closeSuggestion);
          },
          input(e) {
            focusHandle(openSuggestion, closeSuggestion);
          }
        })
      }

      $(() => {
        const selector = [
          'input#woocommerce-product-search-field-0', 
          'input#woocommerce-product-search-field-1'
        ];

        $(selector.join(',')).each(function() {
          searchSuggestion($(this));
        })
        
      })
    })(window, jQuery)
  </script>
  <?php
});

if( function_exists('acf_add_local_field_group') ):

  acf_add_local_field_group(array(
    'key' => 'group_63982a878cdb2',
    'title' => 'Search Suggestion',
    'fields' => array(
      array(
        'key' => 'field_639837ec0d549',
        'label' => 'Search Suggestion Enable',
        'name' => 'search_suggestion_enable',
        'type' => 'true_false',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'default_value' => 0,
        'message' => '',
        'ui' => 0,
        'ui_on_text' => '',
        'ui_off_text' => '',
      ),
      array(
        'key' => 'field_63982ac2eaa1e',
        'label' => 'Search Suggestion',
        'name' => 'search_suggestion',
        'type' => 'textarea',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'default_value' => 'bliss
  tincture
  hash
  vape
  faded',
        'placeholder' => '',
        'maxlength' => '',
        'rows' => '',
        'new_lines' => '',
        'acfe_textarea_code' => 0,
      ),
    ),
    'location' => array(
      array(
        array(
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'theme-notice-page-checkout-settings',
        ),
      ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'left',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
    'acfe_display_title' => '',
    'acfe_autosync' => '',
    'acfe_form' => 0,
    'acfe_meta' => '',
    'acfe_note' => '',
  ));
  
endif;