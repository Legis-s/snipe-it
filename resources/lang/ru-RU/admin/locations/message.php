<?php

return [
    'bulkedit' => [
        'error' => 'Ни одно поле не изменено, обновление не выполнено.',
        'success' => 'Обновлено :count местоположение.|Обновлено :count местоположения.|Обновлено :count местоположений.',
        'warn' => '{1} Измените поля ниже для обновления местоположения. Пустые поля сохранят прежние значения.|[2,*] Измените поля ниже для обновления выбранных местоположений (:count). Пустые поля сохранят прежние значения во всех записях.',
        'show_selected' => 'Выбрано :count местоположение|Выбрано :count местоположения|Выбрано :count местоположений',
        'company_scope_mismatch_partial' => '{1} Компания одного местоположения не изменена, поскольку находящиеся там предметы или пользователи относятся к другим компаниям. Сначала измените или переместите их.|[2,*] Компания не изменена для :count местоположений, поскольку находящиеся там предметы или пользователи относятся к другим компаниям. Сначала измените или переместите их.',
        'company_scope_mismatch_all' => '{1} Местоположение не переназначено. Указанная компания не совпадает с компанией находящихся там предметов или пользователей.|[2,*] Местоположения не переназначены. Указанная компания не совпадает с компанией предметов или пользователей ни в одном из выбранных местоположений (:count).',
        'parent_company_mismatch_partial' => '{1} Родительское местоположение или компания не изменены для одного местоположения: оно оказалось бы в другой компании, чем родительское.|[2,*] Родительское местоположение или компания не изменены для :count местоположений: они оказались бы в другой компании, чем родительское.',
        'parent_company_mismatch_all' => '{1} Изменения не сохранены. Указанное родительское местоположение или компания приведут к несовпадению компаний местоположения и его родителя.|[2,*] Изменения не сохранены. Указанное родительское местоположение или компания приведут к несовпадению компаний во всех выбранных местоположениях (:count).',
    ],

    'does_not_exist' => 'Местоположение не существует.',
    'assoc_users' => 'Это местоположение в не может быть удалено, потому что это местоположение по крайней мере одного объекта или пользователя, имеет назначенные ему активы или является родительским местоположением другого местоположения. Измените записи так, чтобы они не ссылались на это местоположение, и попробуйте снова ',
    'assoc_assets' => 'Это местоположение связано по крайней мере с одним активом и не может быть удалено. Измените активы так, чтобы они не ссылались на это местоположение и попробуйте снова. ',
    'assoc_child_loc' => 'У этого месторасположения является родительским и у него есть как минимум одно месторасположение уровнем ниже. Поэтому оно не может быть удалено. Обновите ваши месторасположения, так чтобы не ссылаться на него, и попробуйте снова. ',
    'assigned_assets' => 'Назначенные активы',
    'sold_assets' => 'Проданные активы',
    'rent_assets' => 'Активы в аренде',
    'current_location' => 'Текущее местоположение',
    'deleted_warning' => 'Это местоположение было удалено. Восстановите его перед попыткой внести какие-либо изменения.',

    'create' => [
        'error' => 'Местоположение не было создано, попробуйте снова.',
        'success' => 'Местоположение создано.',
    ],

    'update' => [
        'error' => 'Местоположение не было обновлено, попробуйте снова',
        'success' => 'Местоположение обновлено.',
    ],

    'restore' => [
        'error' => 'Местоположение не было восстановлено, попробуйте снова',
        'success' => 'Местоположение восстановлено.',
    ],

    'delete' => [
        'confirm' => 'Вы уверены, что хотите удалить это местоположение?',
        'error' => 'При удалении местоположения возникла проблема. Попробуйте снова.',
        'success' => 'Местоположение удалено.',
    ],

    'bulkedit' => [
        'error' => 'Никаких изменений нет, поэтому ничего не обновлено.',
        'success' => 'Location successfully updated.|:count locations successfully updated.',
        'warn' => 'Edit the fields below to update this location. Fields you leave blank will not change on the location.|Edit the fields below to update all :count selected locations. Fields you leave blank will not change on any of them.',
        'show_selected' => '1 selected location|:count selected locations',
        'company_scope_mismatch_partial' => 'The company was not changed on 1 location because items or users at that location belong to different companies. Update or move those first.|The company was not changed on :count locations because items or users at those locations belong to different companies. Update or move those first.',
        'company_scope_mismatch_all' => 'No locations were reassigned. The requested company does not match items or users at the selected location.|No locations were reassigned. The requested company does not match items or users at any of the :count selected locations.',
        'parent_company_mismatch_partial' => 'The parent or company was not changed on 1 location because it would leave the location in a different company than its parent.|The parent or company was not changed on :count locations because it would leave those locations in a different company than their parent.',
        'parent_company_mismatch_all' => 'No changes were saved. The requested parent or company would leave the location in a different company than its parent.|No changes were saved. The requested parent or company would leave every one of the :count selected locations in a different company than their parent.',
    ],

];
