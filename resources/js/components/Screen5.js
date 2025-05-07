// create management account

import {useState} from "@wordpress/element";
import parse from 'html-react-parser';
import csForm from "./Form";
import csCheckoutModalCommon from "./Common";
import React from "react";

const Screen5 = (props) => {

    const companyField = document.getElementById('billing_company');
    const managerDetailsInput = document.getElementById(Coursesource.checkout_enrolment_manager);
    const managerData = JSON.parse(managerDetailsInput.value);
    const [groups, setGroups] = useState([]);

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
        organisation: (companyField) ? companyField.value : '',
        exists : false,
        allocation: false,
    });

    const [formErrors, setFormErrors] = useSetState({
        organisation: {
            error: false,
            message: '',
            validators: [
                {
                    name: 'notEmpty',
                    message: 'The Organisation name cannot be empty'
                },
            ]
        }
    });

    const [continueButtonLabel, setContinueButtonLabel] = useState('Continue');
    const [dataLoading, setDataLoading] = useState(false);
    const [dataLoadingMessage, setDataLoadingMessage] = useState(false);

    const previousScreen = () => {
        props.updateScreenNumber(4)
    }

    const handleOrganisationChange = (event) => {
        setFormState({
            organisation: event.target.value,
            exists: false,
            allocation: false,

        });
        let newFormErrors = {...formErrors};
        newFormErrors.organisation.error = false;
        newFormErrors.organisation.message = null;
        setFormErrors(newFormErrors);
        setContinueButtonLabel("Check Group");
    }

    const clearGroups = () => {
        setGroups([]);
    }

    const onGroupSelect = (event) => {
        setFormState({
            organisation : event.target.value,
            allocation: false,
            exists : false,
        });
        setGroups([]);

        let newFormErrors = {...formErrors};
        newFormErrors.organisation.error = false;
        newFormErrors.organisation.message = '';
        setFormErrors(newFormErrors);
        setContinueButtonLabel('Check Group');
    }

    const handleAllocationChange = (event) => {
        setFormState({
            allocation: !formState.allocation,
        });
        if( formState.allocation ){
            setContinueButtonLabel('Assign to Group');
        }
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

    /**
     *
     * @param groupName
     * @returns {Promise<boolean>}
     */
    const checkGroupExists = async (groupName, formErrors) => {
        let result = false;
        let errorMessage = null;
        setDataLoading(true);
        setContinueButtonLabel("Checking Group Name..");

        let groupExists = await csForm.groupExists( groupName );
        // Exact match on the Group name. Show warnings...
        if( groupExists.result ){
            errorMessage = 'This organisation name already exists. Please choose a different name, or <strong>tick the box below</strong> to assign these Licences to the organisation named above.';
            if( typeof managerData === 'object' && managerData !== null ){
                let email = managerData.email;
                let groupManagers = await getGroupManagers( groupName, email );
                if( groupManagers.length === 0){
                    errorMessage += "<br><br>";
                    errorMessage += "<strong>Please note:</strong> None of the managers of this learning management account appear to be one of your organisation's email addresses.";
                    errorMessage += "<br><br>";
                    errorMessage += "<strong>Proceed with caution:</strong> if you confirm the account name above your learners and their licences will be assigned to this account.";
                }
                setContinueButtonLabel("Confirm Organisation Name");
            }
            result = true;
        }

        // No exact match but there are similarly named Groups in existence..
        if( !groupExists.result && (groupExists.groups.length > 0) ){
            errorMessage = "This exact organisation name cannot be found, but you can select your organisation's account from the list below? Alternatively, please change the name shown above to create a new account.";
            setContinueButtonLabel("Confirm Organisation Name");
            setGroups(groupExists.groups);
            setFormState({
                allocation: false,
                exists: false,
            });
        }

        if( errorMessage ){
            let newFormErrors = {...formErrors};
            newFormErrors.organisation.error = true;
            newFormErrors.organisation.message = errorMessage;
            setFormErrors(newFormErrors);
            result = true;
        }
        setDataLoading(false);
        return result;
    }

    const getGroupManagers = async (groupName, userEmail) => {
        let result = await jQuery.ajax({
            url: Coursesource.ajaxurl,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'group_managers',
                nonce: Coursesource.nonce,
                group_name: groupName,
                user_email: userEmail,
            }
        }).done(function (response) {
            return response;
        });
        if( result.result ){
            return result.managers;
        }else{
            return [];
        }
    }

    const checkGroup = async (event) => {
        event.preventDefault();
        if (formHasErrors()) {
            return;
        }
        let groupExistsResult = await checkGroupExists(formState.organisation, formErrors);

        if ( groupExistsResult ) {
            setFormState( {
                exists: true,
            } );
        }

        if ( groupExistsResult && !formState.allocation ) {
            return;
        }

        managerData.group = formState.organisation.trim();
        props.updateManagerDetails(managerData);
        props.updateScreenNumber(9);
    }

    return (
        <form className='cs-checkout-groups-screen-4'>
            <h4>Choose a Group</h4>
            <p>
                STEP 2 - Name of your organisation's management account. Please amend the below is not correct.
            </p>

            <div className="form-row">
                <input
                    type="text"
                    className="input-text cs-checkout-groups-input"
                    placeholder="Organisation name"
                    value={formState.organisation}
                    required
                    onChange={handleOrganisationChange}
                />
                {formErrors.organisation.error ? (
                    <p className={csForm.formErrorClass('organisation', formErrors)}>
                        {parse(formErrors.organisation.message)}
                    </p>
                ) : null}
            </div>

            {groups.map((group, index) => (
                <div className="form-row" key={index}>
                    <label>
                        <input
                            type="radio"
                            className="input-radio cs-checkout-groups-radio"
                            value={group}
                            id={group}
                            checked={ group === formState.organisation}
                            onChange={onGroupSelect}
                        />
                        {group}
                    </label>
                </div>
            ))}

            { (formState.exists && ( groups.length === 0 ) ) ? (
            <div className="form-row">
                <input
                    type="checkbox"
                    id="allocateLearnerToGroup"
                    className="input-text cs-checkout-groups-input"
                    checked={formState.allocation}
                    onChange={handleAllocationChange}
                />
                <label htmlFor="allocateLearnerToGroup">Allocate my learner enrolments to the learning management account named above.</label>
            </div>
            ) : null}

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
                    onClick={checkGroup}
                >
                    {continueButtonLabel}
                </button>

            </div>

        </form>
    )
}

export default Screen5
