la sp, stack_base
j main

; ==============================================================================
; === DATA SECTION

cLF		equ	10

start_msg defb "Type a character: \0"
true_letter defb " is a letter\0"
false_letter defb " is not a letter\0"
key_msg defb "Key: \0"
out_msg defb "Encrypted character: \0"
end_msg defb "Closing...\0"

align

k defw 3

stack defs 128
stack_base

; === END OF DATA SECTION
; ==============================================================================

align

;TASK A
; def is_letter(character):
;   if character < 'A':
;       return 0
;   if character > 'z':
;       return 0
;   if character <= 'Z':
;       return 1
;   if character < 'a':
;       return 0
;   return 1

is_letter
    ; Your code goes here
    ;jr ra
    
    li t0, 65
    blt a0, t0, check_lower
    

    li t0, 91
    blt a0, t0, letter

check_lower    
    li t0, 97
    blt a0, t0, not_letter
    
    li t0, 123
    blt a0, t0, letter
    
letter
   li a0, 1 
   jr ra
    
not_letter
   li a0, 0 
   jr ra
    
       


; ==============================================================

;TASK B
; def shift_char(character, k):
;   if is_letter(character):
;       get character offset
;       character = character + k
;       if character goes beyond alphabet:
;           wrap around to the beginning of alphabet
;   return character

shift_char
    ; Your code goes here
    ;jr ra
    
    addi sp, sp, -12
    sw ra, 8[sp]
    sw s0, 4[sp]
    sw s1, 0[sp]
    
    mv s0, a0
    mv s1, a1
    
    jal is_letter
    beqz a0, shift_non_letter
    
    li t0, 90
    ble s0, t0, shift_upper
    
    li t1, 97
    j shift_calculate
    
shift_upper
    li t1, 65
    
shift_calculate
    sub t2, s0, t1
    add t2, t2, s1
    
    li t3, 26
    blt t2, t3, shift_no_wrap
    addi t2, t2, -26
    
    
shift_no_wrap
    add a0, t2, t1
    j shift_done
    
    
shift_non_letter
    mv a0, s0
    
    
shift_done
    lw s1, 0[sp]
    lw s0, 4[sp]
    lw ra, 8[sp]
    addi sp, sp, 12
    jr ra


; ==============================================================

; def main():

main
loop_read
    la a0, start_msg
    li a7, 2
    ecall
    
    li a2, cLF
    li a7, 1	
    ecall
    
    beq a0, a2, end_read
    
    li a7, 0
    ecall
    
    mv s0, a0
    jal is_letter
    
    beqz a0, print_no_letter
    
    la a0, true_letter
    j printing
    
print_no_letter
    la a0, false_letter
printing
    li a7, 2
    ecall
    
    li a0, cLF
    li a7, 0	
    ecall
    
    li a7, 2
    la a0, key_msg
    ecall
    
    lw a0, k
    li a7, 3
    ecall
    
    li a0, cLF
    li a7, 0	
    ecall
    
    la a0, out_msg
    li a7, 2
    ecall
    
    lw a1, k    
    mv a0, s0
    jal shift_char
    
    li a7, 0
    ecall
    
    li a0, cLF
    ecall
    
    j loop_read
end_read
    li a0, cLF
    li a7, 0	
    ecall
    la a0, end_msg
    li a7, 2
    ecall   
    li a7, 5
    ecall

