<style>

    #gs-oos-radio .gs-oos-radio-container {
      min-height: 160px;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      margin-bottom: 1rem;
    }

    #gs-oos-radio small{
      display:block;
      margin-left: 8px;
      color: #737373;
      margin-bottom: 4px;
    }

    #order_notes_out_of_stock_field{
      display: none;
    }

    #gs-oos-radio .gs-oos-radio-container .gs-oos-input {
      display: none;
    }

    #gs-oos-radio .gs-oos-radio-container label img{
      max-width: 30px;
      margin: 0;
      margin-left: auto;
    }

    #gs-oos-radio .gs-oos-radio-container label{
      position: relative;
      color: #444;
      border: 1px solid #aaa;
      border-radius: 7px;
      display: flex;
      align-items: center;
      width: 100%;
      padding: 0.5rem 1rem;
      transition: all 0.5s ease;
    }

    #gs-oos-radio .gs-oos-heading {
      margin-left: 8px !important;
      color: #737373;
    }

    #gs-oos-radio .gs-oos-radio-container label:before{
      content: "";
      height: 12px;
      width: 12px;
      border: 2px solid #ccc;
      border-radius: 50%;
      margin-right: 5px;
      transition: all 0.5s ease;
    }

    #gs-oos-radio .gs-oos-radio-container label:hover {
      background-color: #d9d9d9 !important;
    }

    #gs-oos-radio .gs-oos-radio-container .gs-oos-input:checked + label{
      background-color: #eee !important;
      border: 1px solid #046738;
      color: #046738;
    }

    #gs-oos-radio .gs-oos-radio-container .gs-oos-input:checked + label:before{
      background-color: #046738;
      border: 2px solid #046738;
    }

    @media all and (min-width: 550px){
      #gs-oos-radio .gs-oos-radio-container{
        flex-direction: row;
        min-height: 0px;
      }

      #gs-oos-radio .gs-oos-radio-container label{
      width: 32%;
    }

      
    }


</style>

<?php
    $similar_items_icon = ['https://greensociety.cc/wp-content/uploads/2022/10/pink-similar-item.svg'];
    $contact_me_icon = ['https://greensociety.cc/wp-content/uploads/2022/10/contact-me-mail.svg'];
    $credit_me_icon = ['https://greensociety.cc/wp-content/uploads/2022/10/refund-with-points.svg'];
?>

<div id="gs-oos-radio">
<h3 class="gs-oos-heading wfacp-text-left wfacp-normal">What would you like us to do if an item is out of stock?</h3>
<small>&#128721; It doesn't always happen but in the case that it does, you must choose an option:</small>
<div class="gs-oos-radio-container">
  <input id="gs-oos-similar-item" name="gs-oos-option" type="radio" class="gs-oos-input" value="substitute-with-similar-items-recommended-no-email-confirmation-will-be-sent">
  <label for="gs-oos-similar-item">
    Find a similar item
    <img id="similar-items-icon" src="<?php echo $similar_items_icon[0]; ?>"/>
  </label>
  <input id="gs-oos-contact-me" name="gs-oos-option" type="radio" class="gs-oos-input" value="contact-me-will-cause-delays-with-shipping">
  <label for="gs-oos-contact-me">
    Contact me
    <img id="contact-me-icon" src="<?php echo $contact_me_icon[0]; ?>"/>
  </label>
  <input id="gs-oos-credit-me-with-points" name="gs-oos-option" type="radio" class="gs-oos-input" value="credit-account-in-points-for-missing-items">
  <label for="gs-oos-credit-me-with-points">
    Credit with points
    <img id="credit-points-icon" src="<?php echo $credit_me_icon[0]; ?>"/>
  </label>
  </div>
</div>

<script>
  window.addEventListener('DOMContentLoaded', () => {

	const radioButtons = document.querySelectorAll('.gs-oos-input');
	radioButtons.forEach((button) => {
		button.addEventListener('click', (e) => {
			const value = e.target.value;
			const select = document.getElementById('order_notes_out_of_stock');
  
			if (
				value ===
				'substitute-with-similar-items-recommended-no-email-confirmation-will-be-sent'
			) {
				select.selectedIndex = "1";
			} else if (value === 'contact-me-will-cause-delays-with-shipping') {
				select.selectedIndex = "2";
			} else if (value === 'credit-account-in-points-for-missing-items') {
				select.selectedIndex = "3";
			}
		});
	});
});

</script>