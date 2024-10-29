import Choices from "choices.js";
import Swal from "sweetalert2";
import flatpickr from "flatpickr";

// Starts the choices select wrapper
const choices = new Choices('.athena-choices');

// Starts date time picker
const pickers = document.querySelectorAll('.athena-datetime');
pickers.forEach( e => {
    const date = new Date(e.value);
    flatpickr(e, {
        enableTime: true,
        minDate: 'today',
        dateFormat: 'F j Y h:i K',
        defaultDate: date
    });
});

// Handles the menu clicks
const menuLinks = document.querySelectorAll('.athena-menu li a');
const menuBoxes = document.querySelectorAll('.athena-form-inner > div')
if ( menuLinks.length ) {
    menuLinks.forEach( e => {
        e.addEventListener('click', (i) => {
            i.preventDefault();

            if ( ! i.target ) return;

            const target = i.target;
            if ( ! target.classList.contains('active') ) {
                menuLinks.forEach(o => {
                    o.classList.remove('active');
                });


                target.classList.add('active');

                if ( ! target.dataset.trigger ) return;

                menuBoxes.forEach(o => {
                    o.classList.remove('active')
                })

                const divEl = document.getElementById(target.dataset.trigger);
                if ( divEl ) {
                    divEl.classList.add('active');

                    const selects = divEl.querySelectorAll('select');
                    selects.forEach( s => {
                       if ( s.parentElement.offsetParent && s.classList.contains('athena-choices') && ! s.classList.contains('choices__input')) {
                           new Choices(s);
                       }
                    });
                }
            }
        });
    });
}


// Handle submissions
const formSubmissions = document.getElementById('athena-settings');
if ( formSubmissions ) {
    formSubmissions.addEventListener('submit', async e => {
        e.preventDefault();

        Swal.fire({
            title: 'Saving Data...',
            didOpen: () => {
                Swal.showLoading();
            }
        });

        //Saves the tinyMCE data;
        //tinyMCE.triggerSave();

        const formData = new FormData(e.target);
        formData.append('action', 'athena_settings');
        const requestData = new URLSearchParams(formData).toString();

        const response = await fetch(adminjsvars.ajax, {
            method: 'POST',
            body: requestData,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        const result = await response.text();
        let update = result ? {
                title: 'Saved!',
                icon: 'success'
            } :
            {
                title: 'Failed!',
                icon: 'error'
            };

        Swal.hideLoading();
        Swal.update(update);
    });
}

const mainElSelectors = document.querySelectorAll('select[data-watch]');
mainElSelectors.forEach( e => {
    const current = document.querySelector(`div[data-select=${e.value}]`);

    if ( current ) {
        const currentIn = Array.from(current.children).filter(e => {
            return ['INPUT', 'SELECT', 'TEXTAREA'].includes(e.nodeName);
        });

        const isRequired = currentIn[0].dataset.required;
        if (isRequired) {
            currentIn[0].required = true;
        }

        current.style.display = 'block';
    }

    e.addEventListener('change', o => {
        const hidden = document.querySelectorAll(`div[data-activate="${e.dataset.watch}"]`);

        hidden.forEach(i => {
            const selectData = i.dataset.select;
            i.style.display = "none";

            if (selectData === o.target.value) {
                i.style.display = "block";

                const currentInput = Array.from(i.children).filter(p => {
                    return ['INPUT', 'SELECT', 'TEXTAREA'].includes(p.nodeName);
                });

                if ( currentInput.length ) {
                    const required = currentInput[0].dataset.required;
                    if (required) {
                        currentInput[0].required = true;
                    }

                    if (currentInput[0].nodeName === 'SELECT' && !currentInput[0].classList.contains('choices__input')) {
                        new Choices(currentInput[0], {
                            removeItemButton: true
                        });
                    }
                }
            } else {
                const currentInput = Array.from(i.children).filter(p => {
                    return ['INPUT', 'SELECT', 'TEXTAREA'].includes(p.nodeName);
                });
                currentInput[0].required = false;
            }
        });
    });
});
