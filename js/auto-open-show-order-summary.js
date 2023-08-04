window.addEventListener('load', () => {
	if (window.innerWidth <= 991) {
		const showOrderSummary = document.querySelector('.wfacp_mb_cart_accordian');
		showOrderSummary.click();
	}
});
