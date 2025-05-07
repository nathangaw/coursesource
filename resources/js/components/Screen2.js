import {useState} from "@wordpress/element";
import csCheckoutModalCommon from "./Common";
import Screen1 from "./Screen1";
import React from "react";

const Screen2 = (props) => {
    const [selectedOption, setSelectedOption] = useState("no")

    const closeModal = () => {
        csCheckoutModalCommon.hideModal();
    }

    const handleOptionChange = (event) => {
        setSelectedOption(event.target.value)
    }

    const previousScreen = () => {
        props.updateScreenNumber(1)
    }

    const nextScreen = (event) => {
        event.preventDefault();
        if (selectedOption === "yes") {
            props.updateScreenNumber(6)
        } else {
            props.updateScreenNumber(3)
        }
    }

    return (
        <form className='cs-checkout-groups-screen-2'>
            <h4>Does your company already have a learning management account with us?</h4>

            <div className="form-row">
                <label>
                    <input
                        type="radio"
                        className="input-radio cs-checkout-groups-radio"
                        value="yes"
                        checked={selectedOption === "yes"}
                        onChange={handleOptionChange}
                    />
                    Yes
                </label>
            </div>

            <div className="form-row">
                <label>
                    <input
                        type="radio"
                        className="input-radio cs-checkout-groups-radio"
                        value="no"
                        checked={selectedOption === "no"}
                        onChange={handleOptionChange}
                    />
                    No
                </label>
            </div>

            <div className="cs-modal-button-actions">
                {!Coursesource.checkout_enrolment_keys_required ? (
                    <button
                        className="btn btn-prev"
                        onClick={previousScreen}
                    >
                        Prev
                    </button>
                ) : null}

                <button
                    className="btn btn-action"
                    onClick={nextScreen}
                >
                    Next
                </button>

            </div>


        </form>
    )
}

export default Screen2
