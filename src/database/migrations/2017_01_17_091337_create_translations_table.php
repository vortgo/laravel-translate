<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTranslationsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('translations', function(Blueprint $table)
		{
			$table->bigInteger('translation_id', true)->unsigned();
			$table->bigInteger('entity_id')->unsigned()->index('translations_entity_id_idx');
			$table->string('entity_name')->index('translations_entity_name_idx');
			$table->string('entity_attribute');
			$table->string('locale', 2)->index('translations_locale_idx');
			$table->text('value', 65535);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('translations');
	}

}
