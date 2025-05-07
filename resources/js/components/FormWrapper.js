import React from "react";
import {useState, useEffect} from "@wordpress/element";
import Screen1 from "./Screen1";
import Screen2 from "./Screen2";
import Screen3 from "./Screen3";
import Screen4 from "./Screen4";
import Screen5 from "./Screen5";
import Screen6 from "./Screen6";
import Screen7 from "./Screen7";
import Screen8 from "./Screen8";
import Screen9 from "./Screen9";

const CheckoutGroups = (props) => {
    const keysRequired = useState(Coursesource.checkout_enrolment_keys_required);
    const managerDetailsInput = document.getElementById(Coursesource.checkout_enrolment_manager);
    const [accountUpdated, setAccountUpdated] = useState(false);
    const initialScreenNumber = (keysRequired) ? 2 : 1;
    const [screenNumber, setScreenNumber] = useState(initialScreenNumber);

    const updateManagerDetails = (data) => {
        if (managerDetailsInput) {
            managerDetailsInput.value = JSON.stringify(data);
        }
    }

    const updateScreenNumber = (screenNumber) => {
        if (keysRequired && (screenNumber == 1)) {
            screenNumber = 2;
        }
        setScreenNumber(screenNumber)
    }

    const updateAccountUpdatedStatus = (status) => {
        setAccountUpdated(status)
    }

    useEffect(() => {
        if (screenNumber === 9) {
            props.hideModal();
        }
    }, [screenNumber])

    return (
        <div id="cs-groups-inner" className="cs-checkout-groups-modal-inner">
            {screenNumber === 1 ? (
                <Screen1 updateScreenNumber={updateScreenNumber}/>
            ) : null}
            {screenNumber === 2 ? (
                <Screen2 updateScreenNumber={updateScreenNumber}/>
            ) : null}
            {screenNumber === 3 ? (
                <Screen3 updateScreenNumber={updateScreenNumber}/>
            ) : null}
            {screenNumber === 4 ? (
                <Screen4
                    updateScreenNumber={updateScreenNumber}
                    updateAccountUpdatedStatus={updateAccountUpdatedStatus}
                    updateManagerDetails={updateManagerDetails}
                />
            ) : null}
            {screenNumber === 5 ? (
                <Screen5
                    updateScreenNumber={updateScreenNumber}
                    updateManagerDetails={updateManagerDetails}
                />
            ) : null}
            {screenNumber === 6 ? (
                <Screen6
                    updateScreenNumber={updateScreenNumber}
                    updateManagerDetails={updateManagerDetails}
                />
            ) : null}
            {screenNumber === 7 ? (
                <Screen7
                    updateScreenNumber={updateScreenNumber}
                    accountUpdated={accountUpdated}
                />
            ) : null}
            {screenNumber === 8 ? (
                <Screen8
                    updateScreenNumber={updateScreenNumber}
                    accountUpdated={accountUpdated}
                />
            ) : null}
            {screenNumber === 9 ? (
                <Screen9
                    updateScreenNumber={updateScreenNumber}
                    updateAccountUpdatedStatus={updateAccountUpdatedStatus}
                    updateManagerDetails={updateManagerDetails}
                />
            ) : null}
            <span id="cs-modal-close" className="cs-modal-close"
                  onClick={props.hideModal}
            ><span className="cs-modal-close-inner">X</span>
      </span>

        </div>
    )
}

export default CheckoutGroups