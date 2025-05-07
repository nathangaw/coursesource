import {useState} from "@wordpress/element"
import csCheckoutModalCommon from "./Common";

const Screen3 = (props) => {
    const [selectedOption, setSelectedOption] = useState("yes")

    const previousScreen = () => {
        props.updateScreenNumber(1)
    }

    const handleOptionChange = (event) => {
        setSelectedOption(event.target.value)
    }

    const nextScreen = () => {
        if (selectedOption === "yes") {
            props.updateScreenNumber(4)
        } else {
            props.updateScreenNumber(2)
            csCheckoutModalCommon.hideModal();
        }
    }


    return (
        <form className='cs-checkout-groups-screen-3'>
            <h4>Would you like to create a learning management account?</h4>
            <p>
                This will make it easier to track training progress and receive
                certificates.
            </p>

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
                <button
                    className="btn btn-prev"
                    onClick={previousScreen}
                >
                    Prev
                </button>
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

export default Screen3