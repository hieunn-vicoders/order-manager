<?php

use Illuminate\Database\Seeder;
use VCComponent\Laravel\Language\Entities\Language;
use VCComponent\Laravel\Language\Entities\Languageable;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::insert([
            ["name" => "English", "code"=>"en"],
        ]);
        Languageable::insert([
            ["languageable_type" => "order_statuses", "languageable_id"=>"1", "language_id"=>"1", "value"=>"Pending"],
            ["languageable_type" => "order_statuses", "languageable_id"=>"2", "language_id"=>"1", "value"=>"Approved"],
            ["languageable_type" => "order_statuses", "languageable_id"=>"3", "language_id"=>"1", "value"=>"Delivery"],
            ["languageable_type" => "order_statuses", "languageable_id"=>"4", "language_id"=>"1", "value"=>"Complete"],
            ["languageable_type" => "order_statuses", "languageable_id"=>"5", "language_id"=>"1", "value"=>"Cancel"],
            ["languageable_type" => "order_statuses", "languageable_id"=>"6", "language_id"=>"1", "value"=>"Return Order"],
            ["languageable_type" => "order_statuses", "languageable_id"=>"7", "language_id"=>"1", "value"=>"Complain"],
            ["languageable_type" => "order_statuses", "languageable_id"=>"8", "language_id"=>"1", "value"=>"Process Complain"],
        ]);
    }
}
