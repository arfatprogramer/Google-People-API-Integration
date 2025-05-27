import $ from 'jquery';
import './bootstrap';
import toastr from 'toastr';
window.toastr = toastr;
window.$ = window.jQuery = $;

// Auto-click default tab on page load
document.getElementById("defaultOpen").click();

$(document).ready(function () {
    let addressIndex = 1;

    // Add default row on page load
    $('#addRow').on('click', function () {

        let address = $('.firstaddress').val().trim(); // Get and trim the input value
        if (address === '') {
            // alert('Please fill the address field');
            toastr.error(' Please fill in the address field.');
            return; // Stop if input is empty
        }

        let newRow = `
                    <tr>
                    <td class="px-4 py-2">
                        <input type="text" name="addresses[${addressIndex}][address_type]" value="{{ old('addressType', $data->addressType ?? '') }}" class="firstaddress w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300 " placeholder="Type">
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="addresses[${addressIndex}][street]" value="{{ old('street', $data->street ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="Street">
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="addresses[${addressIndex}][area]" value="{{ old('area', $data->area ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="Area">
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="addresses[${addressIndex}][city]" value="{{ old('city', $data->city ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="City">
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="addresses[${addressIndex}][state]" value="{{ old('state', $data->state ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="State">
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="addresses[${addressIndex}][postal_code]" value="{{ old('postalCode', $data->postalCode ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="Postal Code">
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="addresses[${addressIndex}][country]" value="{{ old('country', $data->country ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="Country">
                    </td>
                    <td class="px-4 py-2 flex space-x-2 justify-center">
                        <button class="setPrimary text-blue-500 hover:text-blue-700 cursor-pointer" type="button" title="Set Primary ">🔑</button>
                        <button class="markWarning text-yellow-500 hover:text-yellow-700 cursor-pointer" type="button" title="Warning">⚠️</button>
                        <button class="deleteRow text-red-500 hover:text-red-700 cursor-pointer" type="button" title="Delete">🗑️</button>
                    </td>
                    </tr>`;
        $('#addressTable').append(newRow);
        addressIndex++;
    });

    // Delete row
    $(document).on('click', '.deleteRow', function () {
        $(this).closest('tr').remove();
    });

    // Set as primary
    $(document).on('click', '.setPrimary', function () {
        $('.setPrimary').removeClass('font-bold');
        $(this).addClass('font-bold');
        alert('Set this address as primary!');
    });

    // Mark as warning
    $(document).on('click', '.markWarning', function () {
        $(this).closest('tr').toggleClass('bg-yellow-100');
        alert('Marked address with warning!');
    });


    //---crreate--multiple----Phone-NO---or----Email---address----------

    // Toggle container (+ to ✖)
    $('#email').on('keyup', function () {
        let value2 = $(this).val();
        // console.log(value2);
        let email_json = $('#email_json').val(value2);

    });
    //phone auto fill in phone_json
    $('#phone').on('keyup', function () {
        let getphone = $(this).val();
        // console.log(getphone);
        let phone_json = $('#phone_json2').val(getphone);
        //    console.log(phone_json[0]);
    });

    $('.toggle-container-btn').on('click', function () {
        const $btn = $(this);
        const $target = $($btn.data('target'));

        if ($target.is(':visible')) {
            $target.slideUp();
            $btn.text('+');
        } else {
            $target.slideDown();
            $btn.text('✖');
        }
    });

    $('.toggle-container-input').on('click', function () {
        const $input = $(this);
        console.log($input.val());
        const $targetinput = $($input.data('target-input'));
        console.log($targetinput);

        if ($targetinput.is(':visible')) {
            $targetinput.slideUp();
            // $btn.text('+');
        } else {
            $targetinput.slideDown();
            // $btn.text('✖');
        }
    });




    // Add new input group --- for --- Email -----------Start---------------
    $('.dynamic-container').on('click', '.add-input-btn', function () {
        const $container = $(this).closest('.dynamic-container');

        // Count current input groups BEFORE adding the new one
        let count = $container.find('.input-group').length;

        // Clone the first group
        const $group = $container.find('.input-group').first().clone();

        // Clear input values
        $group.find('input[type="email"]').val('');
        $group.find('input[type="radio"], input[type="checkbox"]').prop('checked', false);

        // Append the cloned group after the last one
        $container.find('.input-group').last().after($group);

        // Update the count after adding
        let newCount = count + 1;
        $('#count').text(newCount); // Display count
    });

    // Delete email input group
    $('.dynamic-container').on('click', '.delete-btn', function () {
        const $container = $(this).closest('.dynamic-container');

        // Only remove if more than one group remains
        if ($container.find('.input-group').length > 1) {
            $(this).closest('.input-group').remove();

            // Update count
            let count = $container.find('.input-group').length;
            $('#count').text(count);
        }
    });


});

