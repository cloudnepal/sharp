<?php

namespace Code16\Sharp\Tests\Unit\Form\Eloquent\Relationships;

use Code16\Sharp\Form\Eloquent\Relationships\BelongsToManyRelationUpdater;
use Code16\Sharp\Tests\Fixtures\Person;
use Code16\Sharp\Tests\Unit\SharpEloquentBaseTestCase;

class BelongsToManyRelationUpdaterTest extends SharpEloquentBaseTestCase
{
    /** @test */
    public function we_can_update_a_belongsToMany_relation()
    {
        $person1 = Person::create(['name' => 'A']);
        $person2 = Person::create(['name' => 'B']);

        $updater = new BelongsToManyRelationUpdater();

        $updater->update($person1, 'friends', [
            ['id' => $person2->id],
        ]);

        $this->assertDatabaseHas('friends', [
            'person1_id' => $person1->id,
            'person2_id' => $person2->id,
        ]);

        $this->assertCount(2, Person::all());
    }

    /** @test */
    public function we_can_update_an_existing_belongsToMany_relation()
    {
        $person1 = Person::create(['name' => 'A']);
        $person2 = Person::create(['name' => 'B']);
        $person3 = Person::create(['name' => 'C']);

        $person1->friends()->sync([
            ['id' => $person2->id],
        ]);

        $updater = new BelongsToManyRelationUpdater();

        $updater->update($person1, 'friends', [
            ['id' => $person3->id],
        ]);

        $this->assertDatabaseHas('friends', [
            'person1_id' => $person1->id,
            'person2_id' => $person3->id,
        ]);

        $this->assertDatabaseMissing('friends', [
            'person1_id' => $person1->id,
            'person2_id' => $person2->id,
        ]);
    }

    /** @test */
    public function we_can_can_create_a_new_related_item()
    {
        $person1 = Person::create(['name' => 'A']);

        $updater = new BelongsToManyRelationUpdater();

        $updater->update($person1, 'friends', [
            ['id' => null, 'name' => 'John Wayne'],
        ]);

        $this->assertDatabaseHas('people', [
            'name' => 'John Wayne',
        ]);

        $person2 = Person::where('name', 'John Wayne')->first();

        $this->assertDatabaseHas('friends', [
            'person1_id' => $person1->id,
            'person2_id' => $person2->id,
        ]);
    }

    /** @test */
    public function we_can_handle_order_in_a_belongsToMany_relation()
    {
        $person1 = Person::create(['name' => 'A']);
        $person2 = Person::create(['name' => 'B']);
        $person3 = Person::create(['name' => 'C']);

        $person1->friends()->sync([
            ['id' => $person2->id, 'order' => 100],
            ['id' => $person3->id, 'order' => 100],
        ]);

        $updater = new BelongsToManyRelationUpdater();
        $updater->update(
            $person1,
            'friends',
            [['id' => $person3->id], ['id' => $person2->id]],
            ['orderAttribute' => 'order']
        );

        $this->assertDatabaseHas('friends', [
            'person1_id' => $person1->id,
            'person2_id' => $person3->id,
            'order' => 1,
        ]);

        $this->assertDatabaseHas('friends', [
            'person1_id' => $person1->id,
            'person2_id' => $person2->id,
            'order' => 2,
        ]);
    }
}
