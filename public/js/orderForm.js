function addWeek(button)
{
    let div = button.closest(".lieferwoche");
    let parent = button.parentNode;
    let parentparent = parent.parentNode;
    parentparent.removeChild(parent);
    parentparent.insertAdjacentHTML("beforeend",document.getElementById('templateButton').value);
    let template = document.getElementById('templateBestellung');
    div.getElementsByClassName('lieferbestellungen')[0].innerHTML = template.value;
    let template2 = document.getElementById('templateLieferant');
    div.parentNode.insertAdjacentHTML("beforeend", template2.value);
    $('select').selectpicker();
}
function submitOrders(button)
{
    const woche = button.closest(".lieferwoche");
    let bestellungen = [];
    let data = {};
    console.log($(woche).find('form'));
    $(woche).find('form').each(function(){
        bestellungen.push($(this).serialize());
    });
    let lieferfield = woche.getElementsByTagName("input")[0];
    data['woche'] = document.getElementById("weekNr").value; //dataset.nr;
    console.log(lieferfield);
    data['lieferant'] = lieferfield.value;
    data['bestellungen'] = bestellungen;
    console.log(data);
    $.ajax({
        url: "/admin/order",
        type:'POST',
        data: data,
        success: function(response) {
            response = JSON.parse(response);
            switch(response['exit_code'])
            {
                case "added":
                case "updated":
                button.getElementsByTagName("i")[0].classList= "fas fa-check"
                setTimeout(function(){
                    button.getElementsByTagName("i")[0].classList= "fas fa-cloud-upload-alt"
                }, 3500);
                    break;
                case "removed":
                    break;
                default:
                button.getElementsByTagName("i")[0].classList= "fas fa-question"
                    break;
            }
        }
    })
}
function lockWeek(button)
{
    const woche = button.closest(".lieferwoche");
    //let bestellungen = [];
    let data = {};
    //console.log($(woche).find('form'));
    //$(woche).find('form').each(function(){
    //    bestellungen.push($(this).serialize());
    //});
    let lieferfield = woche.getElementsByTagName("input")[0];
    //data['woche'] = document.getElementById("weekNr").value; //dataset.nr;
    let week = document.getElementById("weekNr").value;
    //console.log(lieferfield);
    data['lieferant'] = lieferfield.value;
    //data['bestellungen'] = bestellungen;
    //console.log(data);
    $.ajax({
        url: "/admin/lock/"+week,
        type:'POST',
        data: data,
        success: function(response) {
            response = JSON.parse(response);
            switch(response['exit_code'])
            {
                case "locked":
                button.getElementsByTagName("i")[0].classList= "fas fa-lock"
                    break;
                case "unlocked":
                button.getElementsByTagName("i")[0].classList= "fas fa-lock-open"
                    break;
                case "updated":
                    break;
                default:
                button.getElementsByTagName("i")[0].classList= "fas fa-question"
                    break;
            }
        }
    })
}
function loadWeek(button)
{
    window.location.href = "/admin/"+document.getElementById("weekNr").value;
}