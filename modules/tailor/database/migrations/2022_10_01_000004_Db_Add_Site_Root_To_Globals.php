<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class DbAddSiteRootToGlobals extends Migration
{
    public function up()
    {
        Schema::table('tailor_globals', function (Blueprint $table) {
            $table->integer('site_root_id')->nullable()->unsigned()->after('site_id');
        });
    }

    public function down()
    {
        Schema::table('tailor_globals', function (Blueprint $table) {
            $table->dropColumn('site_root_id');
        });
    }
}
