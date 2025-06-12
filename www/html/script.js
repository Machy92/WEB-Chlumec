const articles = document.querySelectorAll('.article'); 
const leftArrow = document.querySelector('.left-arrow');
const rightArrow = document.querySelector('.right-arrow');

let currentIndex = 1; 

function updateHighlight() {
    articles.forEach((article, index) => {
        const relativeIndex = (index - currentIndex + articles.length) % articles.length;

        article.classList.remove('highlighted');
        article.style.opacity = '0.5';
        article.style.transform = 'scale(1)';
        article.style.order = relativeIndex; 

        if (relativeIndex === 1) {
            article.classList.add('highlighted');
            article.style.opacity = '1';
            article.style.transform = 'scale(1.3)';
        }
    });
}

function moveLeft() {
    currentIndex = (currentIndex - 1 + articles.length) % articles.length; 
    updateHighlight();
}

function moveRight() {
    currentIndex = (currentIndex + 1) % articles.length;
    updateHighlight();
}

leftArrow.addEventListener('click', moveLeft);
rightArrow.addEventListener('click', moveRight);

updateHighlight();