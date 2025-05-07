// fetch account using manager's email

import {useState} from "@wordpress/element"
import csForm from "./Form";
import React from "react";
import csCheckoutModalCommon from "./Common";

const Screen6 = (props) => {

    const emailField = document.getElementById('billing_email');
    const [groups, setGroups] = useState([]);
    const [selectedGroup, setSelectedGroup] = useState("");
    const [dataLoading, setDataLoading] = useState(false);
    const [dataLoadingMessage, setDataLoadingMessage] = useState(false)


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
        email: (emailField) ? emailField.value : '',
    });

    const [formErrors, setFormErrors] = useSetState({
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
        }
    });


    const previousScreen = () => {
        props.updateScreenNumber(2)
    }

    const clearGroups = () => {
        setGroups([]);
    }


    const handleEmailChange = (event) => {
        setFormState({
            email: event.target.value,
        });
    }

    const formHasErrors = () => {
        setFormErrors(csForm.validateForm(formState, formErrors));
        let formErrorValues = Object.values(formErrors);
        return formErrorValues.some(formError => formError.error === true);
    }

    const fetchAccount = async (event) => {
        event.preventDefault();
        if (formHasErrors()) {
            return;
        }
        setDataLoading(true);
        setDataLoadingMessage("Searching for account...");
        return await jQuery.ajax({
            url: Coursesource.ajaxurl,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'find_groups_by_email',
                nonce: Coursesource.nonce,
                email: formState.email
            }
        }).done(function (response) {
            let result = false;
            if (response && (response.groups !== null)) {
                setGroups(response.groups);
                setDataLoading(false);
                setDataLoadingMessage("");
            } else {
                setDataLoadingMessage("Cannot find that account");
            }
            return result;
        });
    }

    const onGroupSelect = (event) => {
        setDataLoading(true);
        setSelectedGroup(event.target.value);
        const managerData = {};
        managerData.group = event.target.value;
        managerData.email = formState.email;
        props.updateManagerDetails(managerData);
        setDataLoading(false);
    }

    const nextScreen = (event) => {
        event.preventDefault();
        props.updateScreenNumber(8)
    }

    const noGroup = (event) => {
        event.preventDefault();
        props.updateScreenNumber(7)
    }

    return (
        <form className='cs-checkout-groups-screen-5'>
            {groups.length ? (
                <div className="cs-checkout-groups-choose-account-email">
                    <h4>Select your account</h4>
                    {groups.map((group, index) => (
                        <div className="form-row" key={index}>
                            <label>
                                <input
                                    type="radio"
                                    className="input-radio cs-checkout-groups-radio"
                                    value={group}
                                    id={group}
                                    checked={selectedGroup == group}
                                    onChange={onGroupSelect}
                                />
                                {group}
                            </label>
                        </div>
                    ))}

                    <div className="cs-modal-button-actions">
                        <button
                            className="btn btn-prev"
                            onClick={clearGroups}
                        >
                            Prev
                        </button>

                        <button
                            disabled={selectedGroup.length <= 0}
                            className="btn btn-action"
                            onClick={nextScreen}
                        >Confirm
                        </button>
                    </div>

                </div>
            ) : (
                <div>
                    <h4>Find your account</h4>
                    <p>
                        Please enter the email address of the learning account manager below
                        so that we can link new learners to the correct account.
                    </p>

                    <div className="form-row">
                        <input
                            type="email"
                            className="input-text cs-checkout-groups-input"
                            placeholder="Email address"
                            value={formState.email}
                            onChange={handleEmailChange}
                        />
                        {formErrors.email.error ? (
                            <p className={csForm.formErrorClass('email', formErrors)}>
                                {formErrors.email.message}
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
                            disabled={formState.email.length <= 0}
                            className="btn btn-action"
                            onClick={fetchAccount}
                        >
                            Fetch account
                        </button>

                    </div>
                </div>
            )}
        </form>
    )
}

export default Screen6
