<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');

        if (empty($tableNames)) {
            throw new \Exception(
                'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.'
            );
        }

        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception(
                'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.'
            );
        }

        // Pivot column names (Spatie Permission v6)
        $permissionPivotKey = $columnNames['permission_pivot_key']
            ?? 'permission_id';

        $rolePivotKey = $columnNames['role_pivot_key']
            ?? 'role_id';

        $modelMorphKey = $columnNames['model_morph_key']
            ?? 'model_id';

        $teamForeignKey = $columnNames['team_foreign_key']
            ?? 'team_id';

        /*
         * Permissions table
         */
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->string('group_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        /*
         * Roles table
         */
        Schema::create($tableNames['roles'], function (Blueprint $table) use (
            $teams,
            $teamForeignKey
        ) {
            $table->bigIncrements('id');

            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($teamForeignKey)->nullable();

                $table->index(
                    $teamForeignKey,
                    'roles_team_foreign_key_index'
                );
            }

            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            if ($teams || config('permission.testing')) {
                $table->unique([
                    $teamForeignKey,
                    'name',
                    'guard_name'
                ]);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        /*
         * Model Has Permissions table
         */
        Schema::create(
            $tableNames['model_has_permissions'],
            function (Blueprint $table) use (
                $tableNames,
                $permissionPivotKey,
                $modelMorphKey,
                $teams,
                $teamForeignKey
            ) {
                $table->unsignedBigInteger($permissionPivotKey);

                $table->string('model_type');

                $table->unsignedBigInteger($modelMorphKey);

                $table->index(
                    [$modelMorphKey, 'model_type'],
                    'model_has_permissions_model_id_model_type_index'
                );

                $table->foreign($permissionPivotKey)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                if ($teams) {
                    $table->unsignedBigInteger($teamForeignKey);

                    $table->index(
                        $teamForeignKey,
                        'model_has_permissions_team_foreign_key_index'
                    );

                    $table->primary([
                        $teamForeignKey,
                        $permissionPivotKey,
                        $modelMorphKey,
                        'model_type'
                    ], 'model_has_permissions_permission_model_type_primary');
                } else {
                    $table->primary([
                        $permissionPivotKey,
                        $modelMorphKey,
                        'model_type'
                    ], 'model_has_permissions_permission_model_type_primary');
                }
            }
        );

        /*
         * Model Has Roles table
         */
        Schema::create(
            $tableNames['model_has_roles'],
            function (Blueprint $table) use (
                $tableNames,
                $rolePivotKey,
                $modelMorphKey,
                $teams,
                $teamForeignKey
            ) {
                $table->unsignedBigInteger($rolePivotKey);

                $table->string('model_type');

                $table->unsignedBigInteger($modelMorphKey);

                $table->index(
                    [$modelMorphKey, 'model_type'],
                    'model_has_roles_model_id_model_type_index'
                );

                $table->foreign($rolePivotKey)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                if ($teams) {
                    $table->unsignedBigInteger($teamForeignKey);

                    $table->index(
                        $teamForeignKey,
                        'model_has_roles_team_foreign_key_index'
                    );

                    $table->primary([
                        $teamForeignKey,
                        $rolePivotKey,
                        $modelMorphKey,
                        'model_type'
                    ], 'model_has_roles_role_model_type_primary');
                } else {
                    $table->primary([
                        $rolePivotKey,
                        $modelMorphKey,
                        'model_type'
                    ], 'model_has_roles_role_model_type_primary');
                }
            }
        );

        /*
         * Role Has Permissions table
         */
        Schema::create(
            $tableNames['role_has_permissions'],
            function (Blueprint $table) use (
                $tableNames,
                $permissionPivotKey,
                $rolePivotKey
            ) {
                $table->unsignedBigInteger($permissionPivotKey);

                $table->unsignedBigInteger($rolePivotKey);

                $table->foreign($permissionPivotKey)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->foreign($rolePivotKey)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary([
                    $permissionPivotKey,
                    $rolePivotKey
                ], 'role_has_permissions_permission_id_role_id_primary');
            }
        );

        // Clear permission cache
        app('cache')
            ->store(
                config('permission.cache.store') != 'default'
                    ? config('permission.cache.store')
                    : null
            )
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception(
                'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.'
            );
        }

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};

