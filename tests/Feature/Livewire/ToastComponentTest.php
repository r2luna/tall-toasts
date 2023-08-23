<?php

declare(strict_types=1);

use Livewire\Livewire;
use Usernotnull\Toast\Livewire\ToastComponent;
use Usernotnull\Toast\Notification;
use Usernotnull\Toast\NotificationType;
use Usernotnull\Toast\ToastManager;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

it('renders correctly and updates global render state', function () {
    assertFalse(ToastManager::componentRendered());

    Livewire::test(ToastComponent::class)
        ->assertSee('x-data')
        ->assertSee('ToastComponent');

    assertTrue(ToastManager::componentRendered());
});

it('receives notifications on livewire component', function () {
    $message = 'testing info';
    $title = 'title info';

    toast()
        ->info($message, $title)
        ->push();

    toast()
        ->info($message . ' next', $title)
        ->pushOnNextPage();

    $component = Livewire::test(ToastComponent::class);

    assertEquals($component->get('toasts'), [
        Notification::make($message . ' next', $title),
        Notification::make($message, $title),
    ]);
});

it('renders and pulls data in a blade view', function () {
    expect(ToastManager::componentRendered())
        ->toBeFalse()
        ->and(ToastManager::hasPendingToasts())
        ->toBeFalse();

    $this->get('base-call-from-blade')
        ->assertOk();

    expect(ToastManager::hasPendingToasts())
        ->toBeTrue()
        ->and(Livewire::test(ToastComponent::class)
            ->get('toasts'))
        ->toEqual([
            Notification::make('toast-from-blade', '', NotificationType::$success),
        ])
        ->and(ToastManager::hasPendingToasts())
        ->toBeFalse()
        ->and(ToastManager::componentRendered())
        ->toBeTrue();
});

it('does not render debug toasts in production', function () {
    $message = 'testing debug';
    $title = 'title debug';

    $pushDebug = fn () => toast()->debug($message, $title)->push();

    setEnvironment('production');
    expect($pushDebug())
        ->and(Livewire::test(ToastComponent::class)
            ->get('toasts'))
        ->toEqual([]);

    setEnvironment();
    expect($pushDebug())
        ->and(Livewire::test(ToastComponent::class)
            ->get('toasts'))
        ->toEqual([Notification::make($message, $title, NotificationType::$debug)]);
});

it('does not allow changing prod field', function () {
    $component = Livewire::test(ToastComponent::class);

    expect($component->get('prod'))->toBeFalse();

    $component->set('prod', true);

    expect($component->get('prod'))->toBeFalse();
});
