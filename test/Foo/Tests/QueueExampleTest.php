<?php

namespace Foo\Tests;

use Foo\Bar;

class QueueExampleTest extends Base {

    /**
     * @test
     */
    public function shouldReturnTrue()
    {
        $example = new Bar();
        $this->assertTrue($example->returnTrue());
    }
}
