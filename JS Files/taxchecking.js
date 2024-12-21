document.addEventListener("DOMContentLoaded",function () {
    const form = document. querySelector("form");
    const vehicleNumberInput = document.getElementById("vehicleNumber");
    const customerNameInput = document.getElementById("customerName");

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const vehicleNumber = vehicleNumberInput.value.trim();
        const customerName = customerNameInput.value.trim();

        if (!vehicleNumber || !customerName) {
            alert("Both Vehicle Number and Customer Name are required!");
            return;
        }

        




    })
})