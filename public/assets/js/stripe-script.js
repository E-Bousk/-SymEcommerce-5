const stripe = Stripe("pk_test_51Js6ShGPYQdv064955NrmKkRLTiijeg1sBQJ9veTGnqgqzbWKunx6nuwfdlIsyhNPCiuxF34izOoVgplDXeGIvGa00o2EVK81P");
const checkoutButton = document.getElementById("checkout-button");
const reference_for_path = document.getElementById("checkout-button").dataset.referenceForPath;

checkoutButton.addEventListener('click', function () {
    fetch("/commande/create-session/" + reference_for_path, {
        method: "POST",
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (session) {
        if (session.error === 'order') {
            // redirection
            window.location.replace("/commande")
        } else {
            return stripe.redirectToCheckout({ sessionId: session.id });
        }
    })
    .then(function (result) {
        if (result.error) {
            alert(result.error.message);
        }
    })
    .catch(function (error) {
        console.error("Error:", error);
    });
});