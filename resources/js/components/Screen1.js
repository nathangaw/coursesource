import {useState} from "@wordpress/element";
import csCheckoutModalCommon from "./Common";

const Screen1 = (props) => {
    const [selectedOption, setSelectedOption] = useState("myself")

    const closeModal = () => {
        csCheckoutModalCommon.hideModal();
    }

    const handleOptionChange = (event) => {
        setSelectedOption(event.target.value)
    }

    const nextScreen = (event) => {
        event.preventDefault();
        if (selectedOption === "others") {
            props.updateScreenNumber(2)
        } else {
            closeModal()
        }
    }

    return (
        <form className='cs-checkout-groups-screen-1'>
            <h4>Are you buying for yourself or others?</h4>
            <p>(If you are buying for yourself, we will automatically enrol you on the course/s. If you are buying for
                others, we will send you enrolment&nbsp;keys)</p>

            <div className="form-row">
                <label>
                    <input
                        type="radio"
                        className="input-radio cs-checkout-groups-radio"
                        value="myself"
                        checked={selectedOption === "myself"}
                        onChange={handleOptionChange}
                    />
                    Myself
                </label>
            </div>

            <div className="form-row">
                <label>
                    <input
                        type="radio"
                        className="input-radio cs-checkout-groups-radio"
                        value="others"
                        checked={selectedOption === "others"}
                        onChange={handleOptionChange}
                    />
                    Others
                </label>
            </div>

            <div className="cs-modal-button-actions">

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

export default Screen1
