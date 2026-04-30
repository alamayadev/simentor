<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LinkTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_parent_and_child_and_resolve_relations()
    {
        $parent = Link::factory()->create(['parent_id' => null]);

        $child = Link::factory()->create(['parent_id' => $parent->id]);

        // Reload to ensure relations are available
        $child = Link::find($child->id);
        $parent = Link::find($parent->id);

        $this->assertNotNull($child->parent);
        $this->assertEquals($parent->id, $child->parent->id);

        $this->assertTrue($parent->children->contains(function ($c) use ($child) {
            return $c->id === $child->id;
        }));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_load_recursive_children()
    {
        // parent -> child -> grandchild
        $parent = Link::factory()->create(['parent_id' => null]);
        $child = Link::factory()->create(['parent_id' => $parent->id]);
        $grandchild = Link::factory()->create(['parent_id' => $child->id]);

        $loaded = Link::with('childrenRecursive')->find($parent->id);

        $this->assertTrue($loaded->children->isNotEmpty());
        $this->assertTrue($loaded->children->first()->children->isNotEmpty());
        $this->assertEquals($grandchild->id, $loaded->children->first()->children->first()->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function scope_with_children_count_adds_count()
    {
        $parent = Link::factory()->create(['parent_id' => null]);
        $child1 = Link::factory()->create(['parent_id' => $parent->id]);
        $child2 = Link::factory()->create(['parent_id' => $parent->id]);

        $withCount = Link::withChildrenCount()->find($parent->id);

        $this->assertTrue(isset($withCount->children_count));
        $this->assertEquals(2, $withCount->children_count);
    }
}
