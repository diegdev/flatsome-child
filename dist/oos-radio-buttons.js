window.addEventListener('DOMContentLoaded', () => {
	console.log('oos script loaded');
	const radioButtons = document.querySelectorAll('.gs-oos-input');
	if (!radioButtons || radioButtons === null || radioButtons === undefined)
		return;
	radioButtons.forEach((button) => {
		button.addEventListener('click', (e) => {
			const value = e.target.value;
			const select = document.getElementById('order_notes_out_of_stock');

			if (
				value ===
				'substitute-with-similar-items-recommended-no-email-confirmation-will-be-sent'
			) {
				select.selectedIndex = 0;
			} else if (value === 'contact-me-will-cause-delays-with-shipping') {
				select.selectedIndex = 1;
			} else if (value === 'credit-account-in-points-for-missing-items') {
				select.selectedIndex = 2;
			}
			return;
		});
	});
});
