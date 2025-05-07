import {useState} from "@wordpress/element";

const csCheckoutModalCommon = {
    modal: document.getElementById('cs-modal-checkout-groups'),

    companyField: document.getElementById('billing_company'),

    passwordField: document.getElementById('account_password'),

    managerDetailsField: document.getElementById(Coursesource.checkout_enrolment_manager),

    logged_in: Coursesource.checkout_enrolment_logged_in,

    getModal: function () {
        return document.getElementById('cs-modal-checkout-groups');
    },

    showModal: function () {
        this.getModal().style.display = 'block';
    },

    hideModal: function () {
        this.getModal().style.display = 'none';
    },

    maybeShowModal: function () {
        let logged_in = csCheckoutModalCommon.logged_in;
        if (logged_in && (this.companyField.value.length > 3)) {
            this.getModal().style.display = 'block';
        } else if ((this.companyField.value.length > 3) && (this.passwordField.value.length > 6)) {
            this.getModal().style.display = 'block';
        } else {
            this.getModal().style.display = 'none';
        }
    },

    useSetState: function (initialState = {}) {
        const [state, regularSetState] = useState(initialState);
        const setState = newState => {
            regularSetState(prevState => ({
                ...prevState,
                ...newState
            }));
        };
        return (initialState = {}) => {
            [state, setState]
        };
    },
};

if (!csCheckoutModalCommon.logged_in) {
    csCheckoutModalCommon.passwordField.addEventListener("blur", function (event) {
        csCheckoutModalCommon.maybeShowModal(event.target);
    });
} else {
    csCheckoutModalCommon.companyField.addEventListener("blur", function (event) {
        csCheckoutModalCommon.maybeShowModal(event.target);
    });
}

export default csCheckoutModalCommon;