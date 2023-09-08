<template>
    <div
        :style="{
            'font-family': ['Helvetica Neue', 'Helvetica', 'Arial', ' sans-serif'].join(', '),
            'font-size': '12px',
            'background-color': '#BBB',
            color: '#FFF',
            padding: '2px 5px',
            display: 'inline-block',
        }"
    >
        <slot></slot>
    </div>
</template>
