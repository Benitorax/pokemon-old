(function() {
    var submitButtonElement = document.getElementById('shop_submit');
    var totalBlockElement = document.getElementById('purchase-total-block');
    var totalElement = document.getElementById('purchase-total');
    var pokeballElement = document.getElementById('shop_pokeball');
    var hpPotionElement = document.getElementById('shop_healingPotion');

    var pokeballPrice = 10;
    var hpPotionPrice = 15;
    var walletAmount = document.getElementById('wallet').innerHTML;
    
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
    }
    
    pokeballElement.addEventListener("change", function(e) {
        pokeballTotal = e.target.value;
        var a = calculatePurchaseTotalAndDisplay();
    });
    
    hpPotionElement.addEventListener("change", function(e) {
        hpPotionTotal = e.target.value;
        calculatePurchaseTotalAndDisplay()
    });
})();