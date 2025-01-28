def calculator():
    print("Welcome to the Python Calculator!")
    print("Choose an operation:")
    print("1. Addition (+)")
    print("2. Subtraction (-)")
    print("3. Multiplication (*)")
    print("4. Division (/)")
    print("5. Exit")

    while True:
        # Prompt the user to select an operation
        choice = input("\nEnter the number corresponding to the operation (1/2/3/4/5): ")

        if choice == "5":
            print("Exiting the calculator. Goodbye!")
            break

        if choice in ["1", "2", "3", "4"]:
            print("Enter numbers one by one. Type 'done' when you are finished.")

            numbers = []  # List to store user inputs
            while True:
                user_input = input("Enter a number (or type 'done' to finish): ")
                if user_input.lower() == "done":
                    break
                try:
                    numbers.append(float(user_input))
                except ValueError:
                    print("Invalid input. Please enter a valid number.")

            # Perform the selected operation
            if len(numbers) < 2:
                print("Please enter at least two numbers for the operation.")
                continue

            if choice == "1":
                result = sum(numbers)
                print(f"The sum of the numbers is: {result}")
            elif choice == "2":
                result = numbers[0]
                for num in numbers[1:]:
                    result -= num
                print(f"The result of subtracting the numbers is: {result}")
            elif choice == "3":
                result = 1
                for num in numbers:
                    result *= num
                print(f"The result of multiplying the numbers is: {result}")
            elif choice == "4":
                try:
                    result = numbers[0]
                    for num in numbers[1:]:
                        result /= num
                    print(f"The result of dividing the numbers is: {result}")
                except ZeroDivisionError:
                    print("Error: Division by zero is not allowed.")
        else:
            print("Invalid choice. Please select a valid operation.")

        print("\n---\n")

# Run the calculator
calculator()
