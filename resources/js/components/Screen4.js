// create management account

import {useState} from "@wordpress/element";
import axios from "axios";
import csCheckoutModalCommon from "./Common";
import csForm from "./Form";

const Screen4 = (props) => {
    //Get some sensible defaults...
    const firstNameField = document.getElementById('billing_first_name');
    const lastNameField = document.getElementById('billing_last_name');
    const emailField = document.getElementById('billing_email');
    const passwordField = document.getElementById('account_password');

    // Customise setState so we can work with objects easily...
    const useSetState = (initialState = {}) => {
        const [state, regularSetState] = useState(initialState);
        const setState = newState => {
            regularSetState(prevState => ({
                ...prevState,
                ...newState
            }));
        };
        return [state, setState];
    };

    const [formState, setFormState] = useSetState({
        firstName: (firstNameField) ? firstNameField.value : '',
        lastName: (lastNameField) ? lastNameField.value : '',
        email: (emailField) ? emailField.value : '',
        password: (passwordField) ? passwordField.value : '',
    });

    const [formErrors, setFormErrors] = useSetState({
        firstName: {
            error: false,
            message: '',
            validators: [
                {
                    name: 'notEmpty',
                    message: 'The first name cannot be empty'
                }
            ],
        },
        lastName: {
            error: false,
            message: '',
            validators: [
                {
                    name: 'notEmpty',
                    message: 'The last name cannot be empty'
                },
            ]
        },
        email: {
            error: false,
            message: '',
            validators: [
                {
                    name: 'notEmpty',
                    message: 'The Email cannot be empty'
                },
                {
                    name: 'validEmail',
                    message: 'Please enter a valid email address'
                }
            ]
        },
        password: {
            error: false,
            message: '',
            validators: [
                {
                    name: 'notEmpty',
                    message: 'The Password cannot be empty'
                },
                {
                    name: 'minLength',
                    params: [6],
                    message: 'The password must be 6 characters or longer'
                }
            ]

        },
    });

    const [dataLoading, setDataLoading] = useState(false)
    const [dataLoadingMessage, setDataLoadingMessage] = useState(false)

    const previousScreen = () => {
        props.updateScreenNumber(3)
    }

    const handleEmailChange = (event) => {
        setFormState({
            email: event.target.value,
        });
    }

    const handleFirstNameChange = (event) => {
        setFormState({
            firstName: event.target.value,
        });
    }

    const handleLastNameChange = (event) => {
        setFormState({
            lastName: event.target.value,
        });
    }

    const handlePasswordChange = (event) => {
        setFormState({
            password: event.target.value,
        });
    }


    /**
     *
     * @param value
     * @param length
     * @returns {boolean}
     */
    const isValidLength = (value, length) => {
        return value.length >= length;
    }

    const formHasErrors = () => {
        setFormErrors(csForm.validateForm(formState, formErrors));
        let formErrorValues = Object.values(formErrors);
        return formErrorValues.some(formError => formError.error === true);
    }

    const createAccount = async (event) => {
        event.preventDefault();
        if (formHasErrors()) {
            return;
        }

        //Save the Manager info as JSON to act on during Order processing...
        let valid = true;
        const managerData = {};
        managerData.fname = formState.firstName;
        managerData.lname = formState.lastName;
        managerData.email = formState.email;
        managerData.password = formState.password;
        for (const property in managerData) {
            if (managerData[property].length === 0) {
                valid = false;
            }
        }
        props.updateManagerDetails(managerData);

        if (valid) {
            props.updateScreenNumber(5);
        }
    }

    return (
        <form className='cs-checkout-groups-screen-4'>
            <h4>Create a Learning Management Account</h4>
            <p>STEP 1 - Details of the manager of your learning management account. Please amend the below details if not correct.</p>
            <div className="form-row">
                <input
                    type="email"
                    className="input-text cs-checkout-groups-input"
                    placeholder="Email address of account manager"
                    value={formState.email}
                    required
                    onChange={handleEmailChange}
                />
                {formErrors.email.error ? (
                    <p className={csForm.formErrorClass('email', formErrors)}>
                        {formErrors.email.message}
                    </p>
                ) : null}
            </div>

            <div className="form-row">
                <input
                    type="text"
                    placeholder="First name of account manager"
                    className="input-text cs-checkout-groups-input"
                    value={formState.firstName}
                    required
                    onChange={handleFirstNameChange}
                />
                {formErrors.firstName.error ? (
                    <p className={csForm.formErrorClass('firstName', formErrors)}>
                        {formErrors.firstName.message}
                    </p>
                ) : null}
            </div>

            <div className="form-row">

                <input
                    type="text"
                    className="input-text cs-checkout-groups-input"
                    placeholder="Last name of account manager"
                    value={formState.lastName}
                    required
                    onChange={handleLastNameChange}
                />
                {formErrors.lastName.error ? (
                    <p className={csForm.formErrorClass('lastName', formErrors)}>
                        {formErrors.lastName.message}
                    </p>
                ) : null}
            </div>

            <div className="form-row">

                <input
                    type="password"
                    minLength="6"
                    className="input-text cs-checkout-groups-input"
                    placeholder="Password"
                    value={formState.password}
                    required
                    onChange={handlePasswordChange}
                />
                {formErrors.password.error ? (
                    <p className={csForm.formErrorClass('password', formErrors)}>
                        {formErrors.password.message}
                    </p>
                ) : null}

            </div>

            <div className="error-messages">
                {dataLoading ? (
                    <p className="form-error message-error">{dataLoadingMessage}</p>
                ) : null}
            </div>

            <div className="cs-modal-button-actions">
                <button
                    className="btn btn-prev"
                    onClick={previousScreen}
                >
                    Prev
                </button>

                <button
                    className="btn btn-action"
                    onClick={createAccount}
                >
                    Go to Step 2
                </button>

            </div>

        </form>
    )
}

export default Screen4
