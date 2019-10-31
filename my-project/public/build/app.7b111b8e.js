// When the user scrolls the page, execute myFunction
window.onscroll = function () {
    myFunction()
};
// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;

// Add the sticky class to the header when you reach its scroll position.
// Remove "sticky" when you leave the scroll position
function myFunction() {
    if (window.pageYOffset > sticky) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
}


$('select').select2({  // All the SELECT are put in Select2 Method
    sorter: function (data) { // Order by alphabetical order
        /* Sort data using lowercase comparison */
        return data.sort(function (a, b) {
            a = a.text.toLowerCase();
            b = b.text.toLowerCase();
            if (a > b) {
                return 1;
            } else if (a < b) {
                return -1;
            }
            return 0;
        });
    }
});

function onClickBtnLike(event) {
    event.preventDefault();
    const url = this.href;
    // The value of THIS in a event function is the HTML element that trigger the event himself

    const spanCount = this.querySelector('span.js-likes');
    const icone = this.querySelector('i');

    axios.get(url).then(function (response) { // We get a promess here

        spanCount.innerText = response.data.likes;

        if (icone.classList.contains('fas')) {
            icone.classList.replace('fas', 'far')
        } else {
            icone.classList.replace('far', 'fas')
        }
    }).catch(function (error) {
        if (error.response.status === 403) {
            /* TO DO
            * Affichage message erreur 403 'Vous devez etre connecté"
            * */
        } else {
            /* TO DO
             * Affichage message erreur 404 'Erreur veuillez réessayer ultérieurement"
             * */
        }
    })
}

document.querySelectorAll('a.js-like').forEach(function (link) {

    link.addEventListener('click', onClickBtnLike);
})



// Animate form when click on the button
$('#contactButton').click(e => {
        e.preventDefault();
        $('#contactForm').slideDown();
        $('#contactButton').slideUp();
    }
)