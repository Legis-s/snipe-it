<?php

return [
    'bulk_delete'		=> 'Бөөнөөр устгах активыг баталгаажуулна уу',
    'bulk_restore'      => 'Confirm Bulk Restore Assets', 
  'bulk_delete_help'	=> 'Доорхи их хэмжээний устгалт хийх хөрөнгийг хянаж үзэх. Устгагдсан тохиолдолд эдгээр хөрөнгийг сэргээж болно. Гэхдээ тэдгээр нь одоогоор тэдгээрт хуваарилагдсан хэрэглэгчдэд хамааралгүй болно.',
  'bulk_restore_help'	=> 'Review the assets for bulk restoration below. Once restored, these assets will not be associated with any users they were previously assigned to.',
  'bulk_delete_warn'	=> 'Та устгах гэж байна: asset_count хөрөнгө.',
  'bulk_restore_warn'	=> 'You are about to restore :asset_count assets.',
    'bulk_update'		=> 'Бөөнөөр шинэчлэх актив',
    'bulk_update_help'	=> 'Энэ маягтыг олон дахин нэг зэрэг олон актив шинэчлэх боломжтой. Зөвхөн өөрчлөх шаардлагатай талбаруудыг бөглөөрэй. Хоосон үлдсэн талбарууд өөрчлөгдөхгүй хэвээр үлдэнэ.',
    'bulk_update_warn'	=> 'You are about to edit the properties of a single asset.|You are about to edit the properties of :asset_count assets.',
    'bulk_update_with_custom_field' => 'Note the assets are :asset_model_count different types of models.',
    'bulk_update_model_prefix' => 'On Models', 
    'bulk_update_custom_field_unique' => 'This is a unique field and can not be bulk edited.',
    'checkedout_to'		=> 'Үүнийг шалгах',
    'checkout_date'		=> 'Тооцоо хийх өдөр',
    'checkin_date'		=> 'Checkin Огноо',
    'checkout_to'		=> 'Тооцоо хийх',
    'cost'				=> 'Худалдан авах зардал',
    'create'			=> 'Хөрөнгө үүсгэх',
    'date'				=> 'Худалдан авах өдөр',
    'depreciation'	    => 'Элэгдэл',
    'depreciates_on'	=> 'On',
    'default_location'	=> 'Анхдагч байршил',
    'default_location_phone' => 'Default Location Phone',
    'eol_date'			=> 'EOL Огноо',
    'eol_rate'			=> 'EOL Rate',
    'expected_checkin'  => 'Хүлээгдэж буй хугацаа',
    'expires'			=> 'Хугацаа дуусна',
    'fully_depreciated'	=> 'Бүрэн тооцсон',
    'help_checkout'		=> 'Хэрэв та энэ хөрөнгийг нэн даруй олгохыг хүсвэл дээрх статусын жагсаалтаас "Илгээхэд бэлэн" -ийг сонгоно уу.',
    'mac_address'		=> 'MAC хаяг',
    'manufacturer'		=> 'Үйлдвэрлэгч',
    'model'				=> 'Загвар',
    'months'			=> 'сар',
    'name'				=> 'Хөрөнгийн нэр',
    'notes'				=> 'Тэмдэглэл',
    'order'				=> 'Захиалгын дугаар',
    'qr'				=> 'QR код',
    'requestable'		=> 'Хэрэглэгчид энэ хөрөнгийг шаардаж болно',
    'redirect_to_all'   => 'Return to all :type',
    'redirect_to_type'   => 'Go to :type',
    'redirect_to_checked_out_to'   => 'Go to Checked Out to',
    'select_statustype'	=> 'Статусын төрлийг сонгоно уу',
    'serial'			=> 'Цуваа',
    'status'			=> 'Статус',
    'tag'				=> 'Хөрөнгийн шошго',
    'update'			=> 'Хөрөнгийн шинэчлэлт',
    'warranty'			=> 'Баталгаат',
        'warranty_expires'		=> 'Баталгаат хугацаа дуусах',
    'years'				=> 'жил',
    'asset_location' => 'Update Asset Location',
    'asset_location_update_default_current' => 'Update default location AND actual location',
    'asset_location_update_default' => 'Update only default location',
    'asset_location_update_actual' => 'Update only actual location',
    'asset_not_deployable' => 'That asset status is not deployable. This asset cannot be checked out.',
    'asset_not_deployable_checkin' => 'That asset status is not deployable. Using this status label will checkin the asset.',
    'asset_deployable' => 'That status is deployable. This asset can be checked out.',
    'processing_spinner' => 'Processing... (This might take a bit of time on large files)',
    'optional_infos'  => 'Optional Information',
    'order_details'   => 'Order Related Information',
    'calc_eol'    => 'If nulling the EOL date, use automatic EOL calculation based on the purchase date and EOL rate.',
];
