$(() => {
    const inputCity = $('.city-input');
    const list = $('ul.cities');
    const inputLocation = $('.location-input');
    const listLocations = $('ul.locations');
    let currentCities;
    let currentLocations;
    list.hide();
    $('.btn-add-location').hide();
    inputCity.on('keyup', (e) => {
        if (e.target.value.length > 0) {
            $.ajax({
                url: `http://sortie/api/city/${e.target.value}`,
                success: (response) => {
                    list.empty();
                    currentCities = response;
                    for (let i = 0; i < response.length; i++) {
                        let _li = $("<li></li>").text(`${response[i].name} (${response[i].postalCode})`);
                        _li.on('click',() => {
                            $('#trip_location_city_id').val(response[i].id);
                            $('#trip_location_city_name').val(`${response[i].name} (${response[i].postalCode})`);
                            list.hide();
                            $.ajax({
                                url: `http://sortie/api/locations/${response[i].id}`,
                                method: 'GET',
                                success: (response) => {
                                    currentLocations = response.locations;
                                },
                                error: (response) => {
                                    console.log(response);
                                }
                            });
                        });
                        list.append(_li);
                    }
                },
                error: (response) => {
                    list.empty();
                }
            });
        } else {
            list.empty();
        }
    });
    inputCity.focus(() => {
       list.show();
    });
    inputLocation.on('focus', (e) => {
        for (let i = 0; i < currentLocations.length; i++) {
            let _li = $("<li></li>").text(currentLocations[i].name);
            _li.on('click', () => {
                $('#trip_location_idLocation').val(currentLocations[i].id);
                $('#trip_location_name').val(currentLocations[i].name);
                listLocations.hide();
            });
            listLocations.append(_li);
        }
    });
    $('.submit-new-location').on('click', (e) => {
        console.log(e);
        const request = {
            cityId: $('#trip_location_city_id').val(),
            name: $('#location-name').val(),
            street: $('#location-address').val()
        };
        $.ajax({
            url: `http://sortie/api/locations/add`,
            method: 'POST',
            data: request,
            success: (response) => {
                console.log(response);
                currentLocations.push({
                    id: response.id,
                    name: request.name
                });
                $('#trip_location_idLocation').val(response.id);
                $('#trip_location_name').val(request.name);
            },
            error: (response) => {
                console.log(response);
            }
        })
    });
});