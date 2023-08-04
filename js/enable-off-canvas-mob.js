function handleMobileOffCanvasClick() {
	const cartPopup = document.querySelector('[data-open="#cart-popup"]');

	cartPopup.click();
}

// Check if the viewport width is less than or equal to 849px
if (window.innerWidth <= 849) {
	const cartLink = document.querySelectorAll('a[title="Cart"]')[1];

	cartLink.setAttribute('href', 'javascript:void(0);');
	cartLink.addEventListener('click', handleMobileOffCanvasClick);
}

// Add an event listener for window resize events
window.addEventListener('resize', () => {
	if (window.innerWidth <= 849) {
		const cartLink = document.querySelectorAll('a[title="Cart"]')[1];

		cartLink.setAttribute('href', 'javascript:void(0);');
		cartLink.addEventListener('click', handleMobileOffCanvasClick);
	} else {
		const cartLink = document.querySelectorAll('a[title="Cart"]')[1];

		cartLink.removeEventListener('click', handleMobileOffCanvasClick);
	}
});
