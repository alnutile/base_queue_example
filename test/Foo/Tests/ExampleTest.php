<?php

namespace Foo\Tests;

use Foo\Bar;

class Foo extends Base {

    /**
     * @test
     */
    public function shouldReturnTrue()
    {
        $example = new Bar();
        $this->assertTrue($example->returnTrue());
    }
}
