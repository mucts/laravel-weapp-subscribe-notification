<?php


namespace Friendsmore\LaravelWeAppSubscribeMessage;


use Friendsmore\LaravelWeAppSubscribeNotification\Models\WeAppSubscribeNotifications;

class SubscribeValidate
{
    public static function validate(WeAppSubscribeNotifications $notifications, $data)
    {
        $rules = collect();
        collect($notifications->content)->each(function ($value) use ($rules) {
            $rules->put(sprintf('%s%s', $value['rule'], $value['kid']), ['array', 'min:1']);
            $rules->put(sprintf('%s%s.value', $value['rule'], $value['kid']), self::getRule($value['rule']));
        });
    }

    private static function getRule($rule): array
    {
        $rules = ['nullable'];
        switch ($rule) {
            // 20个以内字符，可汉字、数字、字母或符号组合
            case 'thing':
                $rules += ['string', 'between:1,20'];
                break;
            // 32位以内数字，可带小数
            case 'number':
                $rules += ['numeric', 'between:1,32'];
                break;
            // 32位以内字母
            case 'letter':
                $rules += ['alpha', 'between:1,32'];
                break;
            // 5位以内符号
            case 'symbol':
                $rules += ['string', 'between:1,5'];
                break;
            // 32位以内数字、字母或符号
            case 'character_string':
                $rules += ['string', 'between:1,32'];
                break;
            case 'time':
                $rules += ['date_format:'];
        }
        return $rules;
    }
}