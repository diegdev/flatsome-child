window.addEventListener('DOMContentLoaded', () => {
	const overlay = document.querySelector('.gs-page-overlay');

	if (overlay) {
		window.addEventListener('scroll', () => {
			if (window.scrollY > 350) {
				overlay.style.zIndex = '1';
				overlay.classList.add('gs-page-overlay-on');
			} else {
				overlay.style.zIndex = '-1';
				overlay.classList.remove('gs-page-overlay-on');
			}
		});
	} else {
		return;
	}
});
