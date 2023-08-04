const init = () => {

  if (document.body.classList.contains('tax-product_cat')) {

    const termDescriptionWrapper = document.querySelector('.term-description');
    const description = document.querySelector('.term-description p');


    if (window.innerWidth < 850) {
      const [firstSentence, ...rest] = description.innerHTML.split(". ");

      const expand = document.createElement('span');
      expand.innerText = 'Read More';
      expand.classList.add('prod-cat-expand');
      expand.addEventListener('click', () => descriptionToggle(description, rest, expand));

      description.innerHTML = firstSentence + "... ";
      termDescriptionWrapper.appendChild(expand);
    }

  } else if (document.body.classList.contains('post-type-archive-product')) {

    const termDescriptionWrapper = document.querySelector('.page-description');
    const description = document.querySelector('.page-description p');

    if (window.innerWidth < 850) {
      const [firstSentence, ...rest] = description.innerHTML.split(". ");

      const expand = document.createElement('span');
      expand.innerText = 'Read More';
      expand.classList.add('prod-cat-expand');
      expand.addEventListener('click', () => descriptionToggle(description, rest, expand));

      description.innerHTML = firstSentence + "... ";
      termDescriptionWrapper.appendChild(expand);
    }

  } else {
    return;
  }


}

const descriptionToggle = (description, rest, expand) => {
  description.classList.toggle('show-full-description');

  if (description.classList.contains('show-full-description')) {
    const removePeriods = description.innerHTML.slice(0, description.innerHTML.length - 4);
    const theRest = rest.join(".");
    const fullDescription = `${removePeriods}. ${theRest}`;
    description.innerHTML = fullDescription;
    expand.innerText = 'Read Less';
  } else {

    const [firstSentence, ...rest] = description.innerHTML.split(". ");
    description.innerHTML = firstSentence + "... ";

    expand.innerText = 'Read More';
  }

}

window.addEventListener('DOMContentLoaded', init);