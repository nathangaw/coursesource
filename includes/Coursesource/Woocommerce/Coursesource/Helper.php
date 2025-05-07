<?php

namespace Coursesource\Woocommerce\Coursesource;

use Coursesource\Woocommerce\Settings;

class Helper
{

    /**
     * @param int $course_id
     * @param Api $api
     * @param $vendors
     * @param $attribute_names
     * @return array|null
     */
    public static function parseCourseDataForProductImport( int $course_id, Api $api, $vendors, $attribute_names = [] )
    {
        $import_data = null;
        if( empty( $attribute_names ) ) {
            $attribute_names = [
                'HoursOfTraining' => Settings::getAttributeNameTrainingDuration(),
                'Publisher' => Settings::getAttributeNamePublisher(),
            ];
        }

        $catalogueData = $api->getCatalogueCourse( $course_id );
        if ( isset( $catalogueData->CourseInfo->CourseID ) ) {
            // Why no checking to see if the catalogueData is even halfway sensible?
            $durationData = $api->api_getDurations($course_id);

            $customSKUPrefix = Settings::getProductSkuPrefix();
            $coursesource_id = $catalogueData->CourseInfo->CourseID;
            $sku = $customSKUPrefix . $coursesource_id;
            $product_title = $catalogueData->CourseInfo->Course_Title;
            $product_image = $catalogueData->CourseInfo->Course_Image;
            $hours_of_training = $catalogueData->CourseInfo->Hours_of_Training;
            $vendor_name = $vendors[$catalogueData->CourseInfo->VendorID];
            $product_price = $catalogueData->SellPrice;

            // As of 2024-02-16 Live & Dev API endpoints for getCatalogueCourse endpoint are returning different nodes from the Outline node
            // Make sure we can get a Product Description from either Introduction or HTML nodes
            $product_desc = $catalogueData->Outline->Introduction;
            if (Settings::isAPIModeDev()) {
                $product_desc = $catalogueData->Outline->HTML;
            }

            $import_data = [
                'sku' => $sku,
                'title' => $product_title,
                'image' => $product_image,
                'desc' => $product_desc,
                'price' => $product_price,
                'coursesource_id' => $coursesource_id,
                'attributes' => array(
                    $attribute_names['HoursOfTraining'] => $hours_of_training
                ),
                'hidden_attributes' => array(
                    'CourseID' => $catalogueData->CourseInfo->CourseID,
                    'DurationID' => $durationData[0]->DurationID,
                ),
                'filterable_attributes' => array(
                    $attribute_names['Publisher'] => $vendor_name
                )
            ];
        }

        return $import_data;

    }

}