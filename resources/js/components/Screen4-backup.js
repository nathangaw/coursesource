// create management account

import {useState} from "@wordpress/element";
import axios from "axios";
import csCheckoutModalCommon from "./Common";

const Screen5 = (props) => {
    //Get some sensible defaults...
    const firstNameField = document.getElementById('billing_first_name');
    const lastNameField = document.getElementById('billing_last_name');
    const emailField = document.getElementById('billing_email');
    const companyField = document.getElementById('billing_company');

    const [organisation, setOrganisation] = (companyField) ? useState(companyField.value) : useState("");
    const [firstName, setFirstName] = (firstNameField) ? useState(firstNameField.value) : useState("");
    const [lastName, setLastName] = (lastNameField) ? useState(lastNameField.value) : useState("");
    const [email, setEmail] = (emailField) ? useState(emailField.value) : useState("");
    const [password, setPassword] = useState("");
    const [creatingAccount, setCreatingAccount] = useState(false)
    const [groupNameError, setGroupNameError] = useState(false)
    const [dataLoading, setDataLoading] = useState(false)
    const [formErrors, setFormErrors] = useState({
        organisation: false,
        firstName: false,
        lastName: false,
        emailField: false,
        password: false,
    });

    const previousScreen = () => {
        props.updateScreenNumber(2)
    }

    const handleEmailChange = (event) => {
        setEmail(event.target.value)
    }

    const handleOrganisationChange = (event) => {
        setOrganisation(event.target.value)
    }

    const handleFirstNameChange = (event) => {
        setFirstName(event.target.value)
    }

    const handleLastNameChange = (event) => {
        setLastName(event.target.value)
    }

    const handlePasswordChange = (event) => {
        setPasswordValue(event.target.value);
    }


    const setPasswordValue = (value) => {
        setPassword(value);
        let newFormErrors = {...formErrors};
        if (value.length < 6) {
            newFormErrors.password = true;
        } else {
            newFormErrors.password = false;
        }
        setFormErrors(newFormErrors);
    }


    const formHasErrors = () => {
        // Check for any null field values
        let newFormErrors = {...formErrors};
        for (const [key, value] of Object.entries(formErrors)) {
            let fieldValue = Object.keys({key})[0];
            console.log(key, value, fieldValue);
            if (fieldValue.length === 0) {
                newFormErrors[key] = true;
            }
        }
        setFormErrors(newFormErrors);
        console.log(newFormErrors);
        let formErrorValues = Object.values(formErrors);
        // return true;
        console.log("Has errors", formErrorValues.some(formError => formError === true))
        return true;
        return formErrorValues.some(formError => formError === true);
    }

    const linkManagerToGroup = (group_id) => {
        axios
            .post(
                Coursesource.ajaxurl, {
                    '_ajax_nonce': Coursesource.nonce,
                    'action': 'link_manager_to_group',
                    'email': email,
                    'group_id': group_id,
                }
            )
            .then((response) => {
                // add group id to hidden input
                let groupInputs = document.querySelectorAll("input[id$='group_id']")
                groupInputs.forEach((input) => {
                    input.value = group_id
                })
                setDataLoading(false)
                props.updateScreenNumber(6)
            })
    }

    const createGroup = (group_name) => {
        axios
            .get(
                "https://www.elearningmarketplace.co.uk/wp-json/learnupon/create-group?group_name=" +
                group_name
            )
            .then((response) => {
                if (response.data === false) {
                    // group already exists so need new name
                    setDataLoading(false)
                    setGroupNameError(true)
                    return
                } else {
                    linkManagerToGroup(response.data.id)
                }
            })
    }

    // call api to create group

    const createAccount = (event) => {
        event.preventDefault();
        if (formHasErrors()) {
            return;
        }
        //Save the Manager info as JSON to act on during Order processing...
        let valid = true;
        const managerData = {};
        managerData.group = organisation;
        managerData.fname = firstName;
        managerData.lname = lastName;
        managerData.email = email;
        managerData.password = password;
        for (const property in managerData) {
            if (managerData[property].length === 0) {
                valid = false;
            }
        }
        const managerDetailsInput = csCheckoutModalCommon.managerDetailsField;
        if (managerDetailsInput) {
            managerDetailsInput.value = JSON.stringify(managerData);
        }
        if (valid) {
            props.updateScreenNumber(7);
        }
    }

    return (
        <form className='cs-checkout-groups-screen-4'>
            <h4>Create your account</h4>
            <p>
                Please provide the details below so that we can create your management
                account.
            </p>

            <div className="form-row">
                <input
                    type="text"
                    className="input-text cs-checkout-groups-input"
                    placeholder="Organisation name"
                    value={organisation}
                    required
                    onChange={handleOrganisationChange}
                />
            </div>

            <div className="form-row">
                <input
                    type="email"
                    className="input-text cs-checkout-groups-input"
                    placeholder="Email address of account manager"
                    value={email}
                    required
                    onChange={handleEmailChange}
                />
            </div>

            <div className="form-row">
                <input
                    type="text"
                    placeholder="First name of account manager"
                    className="input-text cs-checkout-groups-input"
                    value={firstName}
                    required
                    onChange={handleFirstNameChange}
                />
            </div>

            <div className="form-row">

                <input
                    type="text"
                    className="input-text cs-checkout-groups-input"
                    placeholder="Last name of account manager"
                    value={lastName}
                    required
                    onChange={handleLastNameChange}
                />
            </div>

            <div className="form-row">

                <input
                    type="password"
                    className="input-text cs-checkout-groups-input"
                    placeholder="Password"
                    value={password}
                    required
                    onChange={handlePasswordChange}
                    onBlur={handlePasswordChange}
                />
                {formErrors.password ? (
                    <p style={{color: "red", paddingTop: "15px"}}>
                        The password should be 6 characters or more
                    </p>
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
                    Create account
                </button>

            </div>

            {groupNameError ? (
                <p style={{color: "red", paddingTop: "15px"}}>
                    Unfortunately that organisation name is already in use. Please try
                    another.
                </p>
            ) : null}

            {dataLoading ? (
                <p style={{paddingTop: "15px"}}>Creating account...</p>
            ) : null}

        </form>
    )
}

export default Screen5
