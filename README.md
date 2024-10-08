
# سیستم کتابفروشی

این سیستم کتابفروشی دارای قابلیت‌های مدیریت کتاب، اعضا، و رزرو کتاب است.

## مدیریت کتاب

- هر کتاب دارای اطلاعات دقیقی مانند عنوان، نویسنده، ژانر، تاریخ انتشار، شابک و قیمت است.
- عملیات CRUD برای کتاب فراهم است.
```
    POST      /api/books ..................... books.store › BookController@store  

    GET|HEAD  /api/books/{book} ..................... books.show › BookController@show  

    PUT|PATCH /api/books/{book} ..................... books.update › BookController@update

    DELETE    /api/books/{book} ..................... books.destroy › BookController@destroy  
```
- لیست شهرها و ژانرها و نویسندگان به صورت دستی در دیتابیس قرار داده شده‌اند.
- API برای دریافت لیست کتاب‌ها با ویژگی‌های مختلف طراحی شده است:
```
    GET|HEAD        /api/books ....................... books.index › BookController@index
```
<div dir="rtl">

- ### کوئری پارامتر هایی که میتوانید استفاده کنید :
    - #### s
        - سرچ در تایتل کتاب ها :
            - /api/books?s=test
    - #### lp & hp
        - فیلتر با  قیمت :
            - /api/books?lp=1200&hp=50000
    - #### genre
        - فیلتر با اسم ژانر :
            - /api/books?genre=ترسناک
    - #### city
        - فیلتر با آی‌دی شهر نویسنده :
            - /api/books?city=1
    - #### sort
        - مرتب سازی بر اساس قیمت :
            - /api/books?sort=asc => نزولی به صعودی
            - /api/books?sort=desc => صعودی به نزولی

</div>

## مدیریت اعضا

- هر عضو دارای نمایه با اطلاعات مانند نام، نام خانوادگی، نوع عضویت، و تاریخ اعتبار عضویت , ادمین بودن یا نبودن است.
- عضویت ورودی با استفاده از OTP انجام می‌شود.
```
    POST            /api/register ...................... AuthController@register
    POST            /api/login ......................... AuthController@login  
```
- بعد از verify کردن OTP، یک توکن JWT به کاربر داده می‌شود.
```
    POST            /api/login/otp ..................... AuthController@validate_otp  
    GET|HEAD        /api/logout ........................ AuthController@logout  
```
- قابلیت خرید عضویت ویژه برای یک ماه وجود دارد.
```
    GET|HEAD        /api/buy_vip ..................... AuthController@buy_vip  
```
- قابلیت حذف توکن‌ها به صورت آنلاین توسط ادمین ها وجود دارد.
```
    DELETE          /api/delete_token/{phone} ....................  AuthController@delete_token
```
- قابلیت throttling برای لاگین کردن وجود دارد. ( هر دو دقیقه پنج درخواست )

## رزرو کتاب

- هر فرد می‌تواند کتابی را به مدت یک هفته رزرو کند.
- برای اعضای ویژه، این مدت به دو هفته افزایش می‌یابد.
- برای اعضای عادی، هزینه‌ی رزرو به ازای هر روز ۱۰۰۰ تومان است.
```
    POST    /api/reserve_book ..................... ReservationController@reserve_book  
```
- تخفیف‌های ویژه برای اعضای عادی وجود دارد:
    - اگر یک ماه گذشته بیش از ۳ کتاب مختلف خوانده باشند ۳۰ درصد تخفیف خواهند گرفت
    - اگر مجموع پرداختی های دو ماه گذشته آن ها بیش از 300 هزار تومان بوده باید رایگان می‌شود
- می‌توان لیست کتاب‌های رزرو شده را مشاهده کرد.
```
    POST    /api/reserved_books .................... ReservationController@reserved_books  
```

## نصب و استفاده

برای استفاده از سیستم، ابتدا موارد زیر را انجام دهید:

1. دریافت پروژه , اجرای دستورات زیر :
```shell
composer install
php artisan key:generate.
php artisan migrate.
```
