window.addEventListener('load', (_) => {
	function main() {
		const divButtons = document.querySelectorAll('[data-value="28-grams"]');

		if (divButtons.length > 1) {
			const selectedClass = 'selected';
			divButtons.forEach((button) => {
				if (!button.classList.contains(selectedClass)) {
					button.click();
					button.classList.remove(selectedClass);
				} else {
					button.classList.remove(selectedClass);
				}
			});
		}
		return;
	}

	setTimeout(() => {
		main();
	}, 500);
});
