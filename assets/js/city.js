(function() {
    var submitButtonElement = document.getElementById('shop_submit');
    var totalBlockElement = document.getElementById('purchase-total-block');
    var walletElement = document.getElementById('wallet');
    var totalElement = document.getElementById('purchase-total');
    var pokeballElement = document.getElementById('shop_pokeball');
    var hpPotionElement = document.getElementById('shop_healingPotion');

    var pokeballPrice = 10;
    var hpPotionPrice = 15;
    
    var walletAmount = walletElement.innerHTML;
    var pokeballTotal = pokeballElement.value;
    var hpPotionTotal = hpPotionElement.value;
    var purchaseTotal = (pokeballElement.value * pokeballPrice) + (hpPotionTotal * hpPotionPrice);
    totalElement.innerHTML = purchaseTotal;
    
    function checkTotalPurchaseWithWallet() {
        if(purchaseTotal > walletAmount) {
            totalBlockElement.classList.add("text-danger");
            totalBlockElement.classList.remove("text-warning");
            submitButtonElement.disabled = true;
        } else {
            totalBlockElement.classList.remove("text-danger");
            totalBlockElement.classList.add("text-warning");
            submitButtonElement.disabled = false;
        }
    }
    checkTotalPurchaseWithWallet();
    
    function calculatePurchaseTotalAndDisplay() {
        purchaseTotal = (pokeballElement.value * pokeballPrice) + (hpPotionTotal * hpPotionPrice);
        totalElement.innerHTML = purchaseTotal;
        checkTotalPurchaseWithWallet();
        updateWallet(purchaseTotal);
    }
    
    pokeballElement.addEventListener("change", function(e) {
        pokeballTotal = e.target.value;
        var a = calculatePurchaseTotalAndDisplay();
    });
    
    hpPotionElement.addEventListener("change", function(e) {
        hpPotionTotal = e.target.value;
        calculatePurchaseTotalAndDisplay()
    });

    function updateWallet(purchaseTotal) {
        var newWalletAmount = walletAmount - purchaseTotal;
        walletElement.innerHTML = newWalletAmount;
    }

    $('#pokeball-popover').popover({
        content: 'Convenient to catch pokemons, but not always effective. Some pokemons are hard to catch even asleep. Have a lot on you!',
    });

    $('#healing-potion-popover').popover({
        content: 'Convenient to cure pokemons. It\'s really annoying when you miss it during your trip or the tournament. Always have them on you!',
    });
})();