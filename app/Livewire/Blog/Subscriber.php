<?php

namespace App\Livewire\Blog;

use App\Models\Subscriber as ModelsSubscriber;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Subscriber extends Component
{

    #[Validate('required|email|unique:subscribers,email')]

    public $email = '';

    public function subscribe() {
        $this->validate();
        
        $subscriber = new ModelsSubscriber([
            'email' => $this->email,
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        $subscriber->save();

        session()->flash('subscribe-success', 'Terima kasih sudah berlangganan');

        $this->email = '';

    }

    public function render()
    {
        return view('livewire.blog.subscriber');
    }
}
